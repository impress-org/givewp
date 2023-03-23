<template id="givewp-welcome-banner-newsletter">
    <style>
        form {
            display: flex;
            flex-wrap:wrap;
            gap:8px;
        }
        input {
            flex:3;
            padding: 12px 24px 12px 16px;
            border-radius: 2px;
            border: solid 1px var(--givewp-grey-300);
            background-color: var(--givewp-shades-white);
        }
        button {
            flex: 1;
            font-size: 14px;
            color: white;
            padding: 12px 24px;
            border-radius: 2px;
            border: solid 1px var(--givewp-green-500);
            background-color: var(--givewp-green-500);
        }
        .message {
            visibility: hidden;
            display: flex;
            flex-direction: row;
            justify-content: flex-start;
            align-items: flex-start;
            gap: 8px;
            margin: 12px 0 4px 0;
            padding: 8px 8px 8px 32px;
            border-radius: 4px;
            background-color: var(--givewp-green-25);
            color: var(--givewp-green-600);
        }
        .screen-reader-text {
            border: 0;
            clip: rect(1px, 1px, 1px, 1px);
            clip-path: inset(50%);
            height: 1px;
            margin: -1px;
            overflow: hidden;
            padding: 0;
            position: absolute;
            width: 1px;
            word-wrap: normal !important;
        }
        .screen-reader-text:focus {
            background-color: #eee;
            clip: auto !important;
            clip-path: none;
            color: #444;
            display: block;
            font-size: 1em;
            height: auto;
            left: 5px;
            line-height: normal;
            padding: 15px 23px 14px;
            text-decoration: none;
            top: 5px;
            width: auto;
            z-index: 100000; /* Above WP toolbar. */
        }
    </style>
    <form>
        <label for="email" class="screen-reader-text"><?php _e('Email Address', 'give'); ?></label>
        <input
            id="email"
            type="email"
            value="<?php esc_attr_e(wp_get_current_user()->user_email); ?>"
            placeholder="<?php esc_attr_e('Enter your email address', 'give'); ?>"
        />
        <button type="submit">
            <?php _e('Submit', 'give'); ?>
        </button>
    </form>
    <div class="message">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M8 .667a7.333 7.333 0 1 0 0 14.666A7.333 7.333 0 0 0 8 .667zm3.472 5.804a.667.667 0 1 0-.943-.942L7 9.057 5.472 7.53a.667.667 0 1 0-.943.942l2 2c.26.26.682.26.943 0l4-4z" fill="#2D802F"/>
        </svg>
        <div style="flex:1;">
            <?php _e('Awesome you’re in! You will be the first to receive updates on Give 3.0 and Next Gen', 'give'); ?>
        </div>
    </div>
</template>

<script>
window.customElements.define('givewp-welcome-banner-newsletter', class extends HTMLElement {

    constructor() {
        super();
        this.success = this.success.bind(this);
        this.subscribe = this.subscribe.bind(this);
        this.handleSubmit = this.handleSubmit.bind(this);
        this.template = document.getElementById('givewp-welcome-banner-newsletter')
    }

    connectedCallback() {
        this.attachShadow({mode: 'open'});
        this.shadowRoot.appendChild(this.template.content.cloneNode(true));

        this.form = this.shadowRoot.querySelector("form");
        this.input = this.shadowRoot.querySelector("input");
        this.message = this.shadowRoot.querySelector(".message");

        this.form.addEventListener("submit", this.handleSubmit);
    }

    handleSubmit(e) {
        e.preventDefault();
        this.subscribe()
            .then(this.success);
    }

    success() {
        this.form.style.display = 'none';
        this.message.style.visibility = 'visible';
    }

    subscribe() {
        return fetch('https://connect.givewp.com/activecampaign/subscribe/next-gen-beta', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'subscribe',
                email: this.input.value,
                first_name: '<?php echo esc_js(wp_get_current_user()->user_firstname); ?>',
                last_name: '<?php echo esc_js(wp_get_current_user()->user_lastname); ?>',
                website_url: '<?php echo esc_js(get_bloginfo('url')); ?>',
                website_name: '<?php echo esc_js(get_bloginfo('sitename')); ?>',
            }),
        })
    }
});
</script>
