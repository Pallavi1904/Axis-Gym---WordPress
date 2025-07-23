import CloseButton from '@elementor/ui/CloseButton';
import { useStorage } from '@site-mailer/globals';
import { date } from '@wordpress/date';
import { useSettings } from '../hooks/use-settings';

const DimissButton = () => {
	const { save, get } = useStorage();
	const { setIsOpened } = useSettings();
	const handleDismiss = async () => {
		if ( get.hasFinishedResolution ) {
			await save( {
				site_mailer_review_data: {
					...get.data.site_mailer_review_data,
					dismissals: get.data.site_mailer_review_data.dismissals + 1,
					hide_for_days: get.data.site_mailer_review_data.hide_for_days + 30,
					last_dismiss: date( 'Y-m-d H:i:s' ),
				},
			} );
		}

		setIsOpened( false );
	};
	return (	<CloseButton onClick={ handleDismiss } /> );
};

export default DimissButton;
