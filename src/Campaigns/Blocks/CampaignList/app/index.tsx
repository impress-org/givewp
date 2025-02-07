import useCampaigns from "../../shared/hooks/useCampaigns";

export default ({attributes}) => {
    const campaigns = useCampaigns({
        ids: attributes?.filterBy?.map((campaign: { value: number }) => campaign.value),
        per_page: attributes?.per_page,
    });

    console.log(campaigns);

    return (
        <div>

        </div>
    )
}
