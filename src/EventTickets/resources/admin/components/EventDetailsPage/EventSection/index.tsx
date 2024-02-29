export default function EventSection() {
    const {event} = window.GiveEventTickets;

    return (
        <section>
            <h1>Event Section</h1>
            <p>{event.title} Details</p>
        </section>
    );
}
