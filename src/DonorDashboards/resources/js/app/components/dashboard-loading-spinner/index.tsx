import React from 'react';

import "./style.scss";

/**
 * @since 3.17.0 reference givewp/src/DonorDashboards/resources/views/donordashboardloader.php
 */
export default function DashboardLoadingSpinner() {


    return (
        <div className={"givewp-donordashboard-loader"}>
            <div className={"givewp-donordashboard-loader_wrapper"}>
                <svg
                    className="givewp-donordashboard-loader_spinner"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 349 348"
                >
                    <style type="text/css">
                        {`.st0 { fill: var(--give-donor-dashboard-accent-color); }`}
                    </style>
                    <path className="st0" d="M25.1,204.57c-13.38,0-24.47-10.6-24.97-24.08C0.04,178.09,0,175.97,0,174C0,77.78,78.28-0.5,174.5-0.5c13.81,0,25,11.19,25,25s-11.19,25-25,25C105.85,49.5,50,105.35,50,174c0,1.37,0.03,2.85,0.1,4.65c0.51,13.8-10.27,25.39-24.07,25.9C25.72,204.56,25.41,204.57,25.1,204.57z" />
                    <path className="st0" d="M174.5,348.5c-13.81,0-25-11.19-25-25c0-13.81,11.19-25,25-25c68.65,0,124.5-55.85,124.5-124.5c0-1.38-0.03-2.85-0.1-4.65c-0.51-13.8,10.26-25.4,24.06-25.91c13.83-0.53,25.4,10.26,25.91,24.06c0.09,2.39,0.13,4.51,0.13,6.49C349,270.22,270.72,348.5,174.5,348.5z" />
                </svg>
            </div>
        </div>
    );
};

