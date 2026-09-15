/**
 * What the server tells the embed about the form it is about to load. Everything here is already
 * on the form's settings and blocks; nothing is measured.
 *
 * @since TBD
 */
export type EmbedShape = {
    design: string;
    header: boolean;
    /** Header parts with real height: the goal bar and an image that is not a background. */
    goal: boolean;
    image: boolean;
    /** Block names in each section, in order. Multi-step designs show one section per step. */
    sections: string[][];
    /** Enabled gateways, one row each in the payment block. */
    gateways: number;
};

/**
 * The core designs the skeleton knows how to sketch. Every other design id, including any an
 * add-on registers, gets the spinner.
 *
 * @since TBD
 */
const KNOWN_DESIGNS = ['classic', 'multi-step', 'two-panel-steps'];

/**
 * @since TBD
 */
export function hasSkeleton(shape: EmbedShape | null | undefined): shape is EmbedShape {
    return Boolean(shape && KNOWN_DESIGNS.includes(shape.design) && Array.isArray(shape.sections));
}

/**
 * The blocks whose real height is far from a single input, so they get their own sketch. Anything
 * else is drawn as a label and one input row.
 *
 * @since TBD
 */
const BLOCK_VARIANTS: Record<string, string> = {
    'givewp/donation-amount': 'amount',
    'givewp/payment-gateways': 'gateways',
    'givewp/donation-summary': 'summary',
};

function Bar({className}: {className: string}) {
    return <span className={`givewp-embed-skeleton__bar ${className}`} />;
}

function Field({block, gateways}: {block: string; gateways: number}) {
    const variant = BLOCK_VARIANTS[block] ?? 'field';

    return (
        <div className={`givewp-embed-skeleton__field givewp-embed-skeleton__field--${variant}`}>
            <Bar className="givewp-embed-skeleton__label" />
            {variant === 'amount' && (
                <>
                    <Bar className="givewp-embed-skeleton__input givewp-embed-skeleton__input--tall" />
                    <div className="givewp-embed-skeleton__levels">
                        {Array.from({length: 6}, (_, i) => (
                            <Bar key={i} className="givewp-embed-skeleton__level" />
                        ))}
                    </div>
                </>
            )}
            {variant === 'gateways' && (
                <>
                    {/* The selected gateway opens its own fields under its row. */}
                    <Bar className="givewp-embed-skeleton__input" />
                    <Bar className="givewp-embed-skeleton__panel" />
                    {Array.from({length: Math.max(gateways - 1, 0)}, (_, i) => (
                        <Bar key={i} className="givewp-embed-skeleton__input" />
                    ))}
                </>
            )}
            {variant === 'summary' && (
                <Bar className="givewp-embed-skeleton__panel givewp-embed-skeleton__panel--tall" />
            )}
            {variant === 'field' && <Bar className="givewp-embed-skeleton__input" />}
        </div>
    );
}

function Header({shape}: {shape: EmbedShape}) {
    return (
        <div className="givewp-embed-skeleton__header">
            <Bar className="givewp-embed-skeleton__title" />
            <Bar className="givewp-embed-skeleton__line" />
            <Bar className="givewp-embed-skeleton__line givewp-embed-skeleton__line--short" />
            {shape.image && <Bar className="givewp-embed-skeleton__image" />}
            {shape.goal && <Bar className="givewp-embed-skeleton__goal" />}
        </div>
    );
}

function Section({blocks, gateways}: {blocks: string[]; gateways: number}) {
    return (
        <div className="givewp-embed-skeleton__section">
            <div className="givewp-embed-skeleton__section-header">
                <Bar className="givewp-embed-skeleton__heading" />
                <Bar className="givewp-embed-skeleton__line givewp-embed-skeleton__line--short" />
            </div>
            {blocks.map((block, i) => (
                <Field key={i} block={block} gateways={gateways} />
            ))}
        </div>
    );
}

function Button() {
    return (
        <div className="givewp-embed-skeleton__section">
            <Bar className="givewp-embed-skeleton__button" />
        </div>
    );
}

/**
 * The step title bar with its progress line, and the secure-donation badge under the button, that
 * both multi-step designs wrap around each step.
 */
function Step({children}: {children: React.ReactNode}) {
    return (
        <div className="givewp-embed-skeleton__form givewp-embed-skeleton__step">
            <div className="givewp-embed-skeleton__step-header">
                <Bar className="givewp-embed-skeleton__step-title" />
                <span className="givewp-embed-skeleton__step-progress" />
            </div>
            {children}
            <Button />
            <Bar className="givewp-embed-skeleton__secure" />
        </div>
    );
}

/**
 * A grey sketch of the form, sized from its settings and blocks, so the page reserves close to
 * the height the form will take instead of jumping when it arrives. The bars are decorative; the
 * wrapping status element carries the loading announcement.
 *
 * Classic shows the header and every section. Multi-step shows only its first step, which is the
 * header when the form has one. Two-panel puts the header beside the first section.
 *
 * @since TBD
 */
export default function EmbedSkeleton({shape}: {shape: EmbedShape}) {
    const [firstSection = []] = shape.sections;

    if (shape.design === 'classic') {
        return (
            <div className="givewp-embed-skeleton givewp-embed-skeleton--classic" aria-hidden="true">
                {shape.header && <Header shape={shape} />}
                <div className="givewp-embed-skeleton__form">
                    {shape.sections.map((blocks, i) => (
                        <Section key={i} blocks={blocks} gateways={shape.gateways} />
                    ))}
                    <Button />
                </div>
            </div>
        );
    }

    if (shape.design === 'two-panel-steps') {
        return (
            <div className="givewp-embed-skeleton givewp-embed-skeleton--two-panel" aria-hidden="true">
                {shape.header && <Header shape={shape} />}
                <Step>
                    <Section blocks={firstSection} gateways={shape.gateways} />
                </Step>
            </div>
        );
    }

    return (
        <div className="givewp-embed-skeleton givewp-embed-skeleton--multi-step" aria-hidden="true">
            <Step>
                {shape.header ? <Header shape={shape} /> : <Section blocks={firstSection} gateways={shape.gateways} />}
            </Step>
        </div>
    );
}
