import useCampaigns from '../../shared/hooks/useCampaigns';

export default ({attributes}) => {
    const campaigns = useCampaigns({
        ids: attributes?.filterBy?.map((item: { value: number }) => item.value),
        page: attributes?.page,
        per_page: attributes?.per_page,
    });

    console.log(campaigns);

    return (
        <div>

        </div>
    )
}
