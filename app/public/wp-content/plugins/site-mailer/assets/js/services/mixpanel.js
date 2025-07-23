import mixpanel from 'mixpanel-browser';

const SHARE_USAGE_DATA = 'share_usage_data';
const MIXPANEL_TOKEN = '150605b3b9f979922f2ac5a52e2dcfe9';

const init = async () => {
	const { siteMailerSettingsData } = window;
	const plan = siteMailerSettingsData?.planData?.plan;

	if ( ! siteMailerSettingsData?.planScope?.includes( SHARE_USAGE_DATA ) ) {
		return;
	}

	await mixpanel.init( MIXPANEL_TOKEN, {
		debug: siteMailerSettingsData.pluginEnv === 'dev',
		track_pageview: false,
		persistence: 'localStorage',
		record_sessions_percent: 50,
	} );

	mixpanel.register( {
		productName: 'app_mailer',
		appType: 'Apps',
		environment: siteMailerSettingsData.pluginEnv,
		is_trial: Boolean( plan?.features?.plan?.toLowerCase().includes( 'trial' ) ),
		plan_type: plan?.name,
		subscription_id: plan?.subscription_id,
	} );

	mixpanel.identify( siteMailerSettingsData?.planData?.user?.id );

	const userData = {
		$email: siteMailerSettingsData?.planData?.user?.email,
		$user_id: siteMailerSettingsData?.planData?.user?.id,
		$subscription_type: plan?.name,
		$subscription_id: plan?.subscription_id,
		$subscription_status: plan?.status,
	};

	mixpanel.people?.set_once( userData );
};

const sendEvent = ( name, event ) => {
	if ( mixpanel.__loaded ) {
		mixpanel.track( name, event );
	}
};

export const mixpanelService = {
	init,
	sendEvent,
};

export const eventNames = {
	helpButtonClicked: 'help_button_clicked',
};
