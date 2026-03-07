<?php

/**
 * Redux Framework Personal Data config.
 * For full documentation, please visit: https://devs.redux.io/
 *
 * @package Redux Framework
 */

defined('ABSPATH') || exit;


Redux::set_section($opt_name, array(
	'title'  => __('Personal Data & Privacy', 'codeweber'),
	'id'     => 'cf7_consent',
	'desc'   => __('Settings required to generate the privacy policy, cookie policy, and personal data consent forms in accordance with Russian Federation law.', 'codeweber'),
	'fields' => array(

		array(
			'id'       => 'sectidon-start2',
			'type'     => 'section',
			'title'    => esc_html__('Basic data', 'codeweber'),
			'indent'   => true, // Indent all options below until the next 'section' option is set.
		),


		array(
			'id'      => 'legal_entity',
			'type'    => 'textarea',
			'rows'    => '3',
			'title'   => __('Legal Entity (Company Name) ', 'codeweber'),
			'desc'    => __('Full name of the legal entity responsible for data processing. <br> [redux_option key="legal_entity"]', 'codeweber'),
			'default' => 'Индивидуальный предприниматель Иванов Иван Иванович',
		),

		array(
			'id'      => 'legal_entity_short',
			'type'    => 'textarea',
			'rows'    => '3',
			'title'   => __('Legal Entity (Company Name) Short', 'codeweber'),
			'desc'    => __('Short name of the legal entity responsible for data processing. <br> [redux_option key="legal_entity_short"]', 'codeweber'),
			'default' => 'ИП Иванов Иван Иванович',
		),

		array(
			'id'      => 'legal_entity_dative',
			'type'    => 'textarea',
			'rows'    => '3',
			'title'   => __('Legal Entity (Company Name) Dative', 'codeweber'),
			'desc'    => __('Full name of the legal entity responsible for data processing Dative. <br> [redux_option key="legal_entity_dative"]', 'codeweber'),
			'default' => 'Индивидуальному предпринимателю Иванову Ивану Ивановичу',
		),
		array(
			'id'      => 'legal_ogrnip',
			'type'    => 'text',
			'title'   => __('OGRN/OGRNIP', 'codeweber'),
			'desc'    => __('[redux_option key="legal_ogrnip"]', 'codeweber'),
			'default' => '325930100000000',
		),

		array(
			'id'      => 'legal_kpp',
			'type'    => 'text',
			'title'   => __('KPP', 'codeweber'),
			'desc'    => __('[redux_option key="legal_kpp"]', 'codeweber'),
			'default' => '771301001',
		),

		array(
			'id'      => 'legal_ogrnip_date',
			'type'    => 'date',
			'title'   => __('OGRN/OGRNIP Date registration', 'codeweber'),
			'desc'    => __('Date of state registration. <br>[redux_option key="legal_ogrnip_date"  format="d.m.Y"]', 'codeweber'),
			'default' => '03/02/2025',
		),

		array(
			'id'      => 'taxpayer_identification_number',
			'type'    => 'text',
			'title'   => __('Taxpayer identification number Person (INN)', 'codeweber'),
			'desc'    => __('[redux_option key="taxpayer_identification_number"]', 'codeweber'),
			'default' => '614026792706',
		),

		array(
			'id'      => 'responsible_person',
			'type'    => 'text',
			'title'   => __('Responsible Person (Full Name)', 'codeweber'),
			'desc'    => __('Full name of the person responsible for personal data processing. <br>[redux_option key="responsible_person"]', 'codeweber'),
			'default' => 'Иванов Иван Иванович',
		),

		array(
			'id'      => 'phone_responsible_person',
			'type'    => 'text',
			'title'   => __('Phone Responsible Person', 'codeweber'),
			'desc'    => __('Phone of the person responsible for personal data processing. <br>[redux_option key="phone_responsible_person"]', 'codeweber'),
			'default' => '+7 999 999 99 99',
		),

		array(
			'id'      => 'email_responsible_person',
			'type'    => 'text',
			'title'   => __('E-mail Responsible Person', 'codeweber'),
			'desc'    => __('E-Mail of the person responsible for personal data processing. <br>[redux_option key="email_responsible_person"]', 'codeweber'),
			'default' => 'test@yandex.com',
		),

		array(
			'id'      => 'storage_address',
			'type'    => 'textarea',
			'rows'    => '3',
			'title'   => __('Data Storage Address', 'codeweber'),
			'desc'    => __('Physical or legal address where personal data is stored. <br>[redux_option key="storage_address"]', 'codeweber'),
			'default' => '127287, г. Москва, ул. Хуторская 2-я, д. 38А, стр. 26',
		),
	),
));


Redux::set_section($opt_name, array(
	'title'  => __('Consent Data', 'codeweber'),
	'id'     => 'consent_data',
	'subsection' => true,
	'fields' => array(

			array(
				'id'      => 'list_of_personal_data',
				'type'    => 'textarea',
				'rows'    => '3',
				'title'   => __('List of collected personal data', 'codeweber'),
				'subtitle'    => __('For consent to the processing of personal data', 'codeweber'),
				'desc'    => __('[redux_option key="list_of_personal_data"]', 'codeweber'),
				'default' => __("Last name, first name, patronymic, email address, age, driving experience, telephone number, driver's license details.", "codeweber"),
			),


			array(
				'id'       => 'personal_data_actions',
				'type'     => 'checkbox',
				'title'    => __('Personal Data Processing Actions', 'codeweber'),
				'subtitle'    => __('For consent to the processing of personal data', 'codeweber'),
				'desc'     => __('[redux_option key="consent_purpose" list="inline"]<br>[redux_option key="consent_purpose" list="ul"]', 'codeweber'),
				'options'  => array(
					'collection'     => __('Collection Data', 'codeweber'),
					'recording'      => __('Recording Data', 'codeweber'),
					'systematization' => __('Systematization Data', 'codeweber'),
					'accumulation'   => __('Accumulation Data', 'codeweber'),
					'storage'        => __('Storage Data', 'codeweber'),
					'updating'       => __('Updating (clarification, modification) Data', 'codeweber'),
					'extraction'     => __('Extraction Data', 'codeweber'),
					'usage'          => __('Usage Data', 'codeweber'),
					'transfer'       => __('Transfer (distribution, provision, access) Data', 'codeweber'),
					'blocking'       => __('Blocking Data', 'codeweber'),
					'deletion'       => __('Deletion Data', 'codeweber'),
					'destruction'    => __('Destruction Data', 'codeweber'),
				),
				'default'  => array(
					'collection',
					'recording',
					'systematization',
					'accumulation',
					'storage',
					'updating',
					'extraction',
					'usage',
					'transfer',
					'blocking',
					'deletion',
					'destruction',
				),
			),

	)
)
	);


Redux::set_section(
	$opt_name,
	array(
		'title'  => __('Privacy Data', 'codeweber'),
		'id'     => 'privacy_data',
		'subsection' => true,
		'fields' => array(


			array(
				'id'       => 'purposes_of_collecting_personal_data',
				'type'     => 'section',
				'title'    => esc_html__('5.3. The Operator processes personal data for the following purposes:', 'codeweber'),
				'indent'   => true, // Indent all options below until the next 'section' option is set.
			),


			array(
				'id'       => 'text_of_purpose_of_collecting_personal_data',
				'type'     => 'editor',
				'title'    => esc_html__('Text for paragraph 5.3 of the personal data processing policy', 'codeweber'),
				'subtitle' => esc_html__('For the policy on the processing of personal data', 'codeweber'),
				'args'   => array(
					'teeny'            => true,
					'textarea_rows'    => 12
				),
				'default'  => '<ul>
 	                                       <li>Подготовка, заключение и исполнение гражданско-правового договора;</li>
 	                                       <li>Обеспечение соблюдения страхового законодательства РФ;</li>
 	                                       <li>Использование Сайта, в том числе обеспечение бесперебойной работы Сайта, улучшение пользовательского опыта, ведение статистики посещений;</li>
 	                                       <li>Получение информации о стоимости услуг или иной обратной связи от Оператора, в том числе информирование о статусе заявок (заказов) и статусе предоставления услуг;</                                          li>
 	                                       <li>Направление рекламных рассылок</li>
                                           </ul>',
			),

			array(
				'id'       => 'section_pdn_data',
				'type'     => 'section',
				'title'    => esc_html__('7.2. The operator processes the following personal data:', 'codeweber'),
				'indent'   => true, // Indent all options below until the next 'section' option is set.
			),

			array(
				'id'       => 'section_pdn_data721',
				'type'     => 'section',
				'title'    => '7.2.1.' .' '.  esc_html__('Purpose of processing', 'codeweber'),
				'indent'   => true, // Indent all options below until the next 'section' option is set.
			),

			array(
				'id'      => 'title_pdn_data721',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('Title Purpose of processing', 'codeweber'),
				'default' => '',
			),

			array(
				'id'      => 'cat_sub_pdn_data721',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('Category of personal data subjects:', 'codeweber'),
				'default' => '',
			),

			array(
				'id'      => 'list_pdn_data721',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('List of personal data processed:', 'codeweber'),
				'default' => '',
			),

			array(
				'id'      => 'cat_pdn_data721',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('Category of personal data processed:', 'codeweber'),
				'default' => '',
			),
			array(
				'id'      => 'method_pdn_data721',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('Method of processing personal data:', 'codeweber'),
				'default' => '',
			),
			array(
				'id'      => 'period_pdn_data721',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('Personal data processing period:', 'codeweber'),
				'default' => '',
			),
			array(
				'id'      => 'destruction_pdn_data721',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('Method of destruction:', 'codeweber'),
				'default' => '',
			),


			array(
				'id'       => 'section_pdn_data722',
				'type'     => 'section',
				'title'    => '7.2.2.' . ' ' .  esc_html__('Purpose of processing', 'codeweber'),
				'indent'   => true, // Indent all options below until the next 'section' option is set.
			),

			array(
				'id'      => 'title_pdn_data722',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('Title Purpose of processing', 'codeweber'),
				'default' => '',
			),

			array(
				'id'      => 'cat_sub_pdn_data722',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('Category of personal data subjects:', 'codeweber'),
				'default' => '',
			),

			array(
				'id'      => 'list_pdn_data722',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('List of personal data processed:', 'codeweber'),
				'default' => '',
			),

			array(
				'id'      => 'cat_pdn_data722',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('Category of personal data processed:', 'codeweber'),
				'default' => '',
			),
			array(
				'id'      => 'method_pdn_data722',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('Method of processing personal data:', 'codeweber'),
				'default' => '',
			),
			array(
				'id'      => 'period_pdn_data722',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('Personal data processing period:', 'codeweber'),
				'default' => '',
			),
			array(
				'id'      => 'destruction_pdn_data722',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('Method of destruction:', 'codeweber'),
				'default' => '',
			),

			array(
				'id'       => 'section_pdn_data723',
				'type'     => 'section',
				'title'    => '7.2.3.' . ' ' .  esc_html__('Purpose of processing', 'codeweber'),
				'indent'   => true, // Indent all options below until the next 'section' option is set.
			),

			array(
				'id'      => 'title_pdn_data723',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('Title Purpose of processing', 'codeweber'),
				'default' => '',
			),

			array(
				'id'      => 'cat_sub_pdn_data723',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('Category of personal data subjects:', 'codeweber'),
				'default' => '',
			),

			array(
				'id'      => 'list_pdn_data723',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('List of personal data processed:', 'codeweber'),
				'default' => '',
			),

			array(
				'id'      => 'cat_pdn_data723',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('Category of personal data processed:', 'codeweber'),
				'default' => '',
			),
			array(
				'id'      => 'method_pdn_data723',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('Method of processing personal data:', 'codeweber'),
				'default' => '',
			),
			array(
				'id'      => 'period_pdn_data723',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('Personal data processing period:', 'codeweber'),
				'default' => '',
			),
			array(
				'id'      => 'destruction_pdn_data723',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('Method of destruction:', 'codeweber'),
				'default' => '',
			),

			array(
				'id'       => 'section_pdn_data724',
				'type'     => 'section',
				'title'    => '7.2.4.' . ' ' .  esc_html__('Purpose of processing', 'codeweber'),
				'indent'   => true, // Indent all options below until the next 'section' option is set.
			),

			array(
				'id'      => 'title_pdn_data724',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('Title Purpose of processing', 'codeweber'),
				'default' => '',
			),

			array(
				'id'      => 'cat_sub_pdn_data724',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('Category of personal data subjects:', 'codeweber'),
				'default' => '',
			),

			array(
				'id'      => 'list_pdn_data724',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('List of personal data processed:', 'codeweber'),
				'default' => '',
			),

			array(
				'id'      => 'cat_pdn_data724',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('Category of personal data processed:', 'codeweber'),
				'default' => '',
			),
			array(
				'id'      => 'method_pdn_data724',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('Method of processing personal data:', 'codeweber'),
				'default' => '',
			),
			array(
				'id'      => 'period_pdn_data724',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('Personal data processing period:', 'codeweber'),
				'default' => '',
			),
			array(
				'id'      => 'destruction_pdn_data724',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('Method of destruction:', 'codeweber'),
				'default' => '',
			),

			array(
				'id'       => 'section_pdn_data725',
				'type'     => 'section',
				'title'    => '7.2.5.' . ' ' .  esc_html__('Purpose of processing', 'codeweber'),
				'indent'   => true, // Indent all options below until the next 'section' option is set.
			),

			array(
				'id'      => 'title_pdn_data725',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('Title Purpose of processing', 'codeweber'),
				'default' => '',
			),

			array(
				'id'      => 'cat_sub_pdn_data725',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('Category of personal data subjects:', 'codeweber'),
				'default' => '',
			),

			array(
				'id'      => 'list_pdn_data725',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('List of personal data processed:', 'codeweber'),
				'default' => '',
			),

			array(
				'id'      => 'cat_pdn_data725',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('Category of personal data processed:', 'codeweber'),
				'default' => '',
			),
			array(
				'id'      => 'method_pdn_data725',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('Method of processing personal data:', 'codeweber'),
				'default' => '',
			),
			array(
				'id'      => 'period_pdn_data725',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('Personal data processing period:', 'codeweber'),
				'default' => '',
			),
			array(
				'id'      => 'destruction_pdn_data725',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('Method of destruction:', 'codeweber'),
				'default' => '',
			),

			array(
				'id'       => 'section_pdn_data726',
				'type'     => 'section',
				'title'    => '7.2.6.' . ' ' .  esc_html__('Purpose of processing', 'codeweber'),
				'indent'   => true, // Indent all options below until the next 'section' option is set.
			),

			array(
				'id'      => 'title_pdn_data726',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('Title Purpose of processing', 'codeweber'),
				'default' => '',
			),

			array(
				'id'      => 'cat_sub_pdn_data726',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('Category of personal data subjects:', 'codeweber'),
				'default' => '',
			),

			array(
				'id'      => 'list_pdn_data726',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('List of personal data processed:', 'codeweber'),
				'default' => '',
			),

			array(
				'id'      => 'cat_pdn_data726',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('Category of personal data processed:', 'codeweber'),
				'default' => '',
			),
			array(
				'id'      => 'method_pdn_data726',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('Method of processing personal data:', 'codeweber'),
				'default' => '',
			),
			array(
				'id'      => 'period_pdn_data726',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('Personal data processing period:', 'codeweber'),
				'default' => '',
			),
			array(
				'id'      => 'destruction_pdn_data726',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('Method of destruction:', 'codeweber'),
				'default' => '',
			),

			array(
				'id'       => 'section_pdn_data727',
				'type'     => 'section',
				'title'    => '7.2.7.' . ' ' .  esc_html__('Purpose of processing', 'codeweber'),
				'indent'   => true, // Indent all options below until the next 'section' option is set.
			),

			array(
				'id'      => 'title_pdn_data727',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('Title Purpose of processing', 'codeweber'),
				'default' => '',
			),

			array(
				'id'      => 'cat_sub_pdn_data727',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('Category of personal data subjects:', 'codeweber'),
				'default' => '',
			),

			array(
				'id'      => 'list_pdn_data727',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('List of personal data processed:', 'codeweber'),
				'default' => '',
			),

			array(
				'id'      => 'cat_pdn_data727',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('Category of personal data processed:', 'codeweber'),
				'default' => '',
			),
			array(
				'id'      => 'method_pdn_data727',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('Method of processing personal data:', 'codeweber'),
				'default' => '',
			),
			array(
				'id'      => 'period_pdn_data727',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('Personal data processing period:', 'codeweber'),
				'default' => '',
			),
			array(
				'id'      => 'destruction_pdn_data727',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('Method of destruction:', 'codeweber'),
				'default' => '',
			),

			array(
				'id'       => 'section_pdn_data728',
				'type'     => 'section',
				'title'    => '7.2.8.' . ' ' .  esc_html__('Purpose of processing', 'codeweber'),
				'indent'   => true, // Indent all options below until the next 'section' option is set.
			),

			array(
				'id'      => 'title_pdn_data728',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('Title Purpose of processing', 'codeweber'),
				'default' => '',
			),

			array(
				'id'      => 'cat_sub_pdn_data728',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('Category of personal data subjects:', 'codeweber'),
				'default' => '',
			),

			array(
				'id'      => 'list_pdn_data728',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('List of personal data processed:', 'codeweber'),
				'default' => '',
			),

			array(
				'id'      => 'cat_pdn_data728',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('Category of personal data processed:', 'codeweber'),
				'default' => '',
			),
			array(
				'id'      => 'method_pdn_data728',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('Method of processing personal data:', 'codeweber'),
				'default' => '',
			),
			array(
				'id'      => 'period_pdn_data728',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('Personal data processing period:', 'codeweber'),
				'default' => '',
			),
			array(
				'id'      => 'destruction_pdn_data728',
				'type'    => 'textarea',
				'rows'    => '2',
				'title'   => __('Method of destruction:', 'codeweber'),
				'default' => '',
			),

		)
	)
);


Redux::set_section(
	$opt_name,
	array(
		'title'  => __('Cookie Data', 'codeweber'),
		'id'     => 'cookie_data',
		'subsection' => true,
		'fields' => array(

			array(
				'id'       => 'enable_cookie_banner',
				'type'     => 'switch',
				'title'    => esc_html__('Enable Cookie Banner', 'codeweber'),
				'default'  => true,
			),

			array(
				'id'      => 'cookie_expiration_date',
				'type'    => 'text',
				'title'   => __('Cookie Expiration Date', 'codeweber'),
				'default' => '365',
			),

			array(
				'id'      => 'welcome_text_cookie_banneer',
				'type'    => 'editor',
				'args'   => array(
					'teeny'            => true,
					'textarea_rows'    => 10
				),
				'title'   => __('Welcome Text Cookie Banner', 'codeweber'),
				'default' => __('This website uses cookies to ensure its uninterrupted operation and to remember selected settings, as well as analytics tools (Yandex.Metrica). By continuing to use the site, you confirm your consent to the processing and use of cookies in your browser in accordance with the <a href="[url_cookie-policy]">Cookie Usage Policy</a>', 'codeweber'),
			),
		)
	)
);



/**
 * Redux Framework Cookie Scanner config with i18n.
 * For full documentation, please visit: https://devs.redux.io/
 *
 * @package Redux Framework
 */


defined('ABSPATH') || exit;

// Суффикс файлов cookie по языку: '' (английский/по умолчанию) или '-ru' (русский)
function codeweber_cookie_scanner_file_suffix() {
    return (strpos(get_locale(), 'ru') !== false) ? '-ru' : '';
}

$cookie_scanner_suffix = codeweber_cookie_scanner_file_suffix();
$components_dir = get_template_directory() . '/components';

// Библиотека: название → назначение (только чтение). Файл по языку: cookies-known.json или cookies-known-ru.json
$known_cookies_file = $components_dir . '/cookies-known' . $cookie_scanner_suffix . '.json';
if (!file_exists($known_cookies_file)) {
    $known_cookies_file = $components_dir . '/cookies-known.json';
}
$known_cookies = [];
if (file_exists($known_cookies_file)) {
    $json_content = file_get_contents($known_cookies_file);
    $known_cookies = json_decode($json_content, true);
    if (!is_array($known_cookies)) {
        $known_cookies = [];
    }
}

// Результаты сканов по языку: cookies-found.json или cookies-found-ru.json
$found_cookies_file = $components_dir . '/cookies-found' . $cookie_scanner_suffix . '.json';
if (!file_exists($found_cookies_file)) {
    $found_cookies_file = $components_dir . '/cookies-found.json';
}
$found_cookies = [];
if (file_exists($found_cookies_file)) {
    $found_content = file_get_contents($found_cookies_file);
    $found_cookies = json_decode($found_content, true);
    if (!is_array($found_cookies)) {
        $found_cookies = [];
    }
}

// Куки админки/WordPress/Redux — не показывать в сканере фронта (на фронте их быть не должно для политики)
$cookie_scanner_admin_blocklist = [
    'redux',
    'wp-settings',
    'wordpress_logged_in',
    'wordpress_sec',
    'wordpress_test_cookie',
    'wp-postpass',
    'wp-settings-',
];

// Код внутри Redux::set_section
Redux::set_section($opt_name, [
    'title'      => __('Cookie Scanner', 'codeweber'),
    'id'         => 'cookie_scanner',
    'desc'       => __('Scan and display browser cookies.', 'codeweber'),
    'subsection' => true,
    'fields'     => [
        [
            'id'      => 'cookie_scanner_shortcode_info',
            'type'    => 'info',
            'style'   => 'info',
            'title'   => __('Output cookie table on the site', 'codeweber'),
            'desc'    => '<p>' . __('To display the list of cookies (from the library and scan results) on any page or post, use the shortcode:', 'codeweber') . '</p>' .
                '<p><code>[codeweber_cookie_table]</code></p>' .
                '<p>' . __('Insert this shortcode into the content of the page (e.g. Cookie Policy). The table will show cookies for the current site language (cookies-known.json / cookies-found.json or their -ru variants).', 'codeweber') . '</p>',
        ],
        [
            'id'      => 'cookie_scan_raw',
            'type'    => 'raw',
            'title'   => __('Scan Frontend Cookies', 'codeweber'),
            'content' => '
                <p class="description">' . esc_html__('Open a page in a popup to read cookies set on that page. Use the homepage, cart, checkout, or any URL of your site.', 'codeweber') . '</p>
                <p style="margin-bottom:8px;">
                    <label for="cookie-scan-url" style="margin-right:8px;">' . esc_html__('URL to scan:', 'codeweber') . '</label>
                    <input type="text" id="cookie-scan-url" value="/" placeholder="/" style="width:320px;" />
                </p>
                <button type="button" class="button button-primary" id="scan-frontend-cookies-btn">' .
                    __('Scan Frontend Cookies', 'codeweber') .
                '</button>
                <div id="cookie-frontend-results" style="margin-top:15px; max-height: 300px; overflow-x: auto; overflow-y: auto; font-family: monospace; width: 100%;"></div>
                <p id="cookie-export-actions" style="margin-top:12px; display:none;">
                    <button type="button" class="button" id="cookie-export-json-btn">' . esc_html__('Export to JSON', 'codeweber') . '</button>
                    <button type="button" class="button" id="cookie-save-file-btn">' . esc_html(sprintf(__('Save to %s', 'codeweber'), 'cookies-found' . $cookie_scanner_suffix . '.json')) . '</button>
                    <button type="button" class="button" id="cookie-clear-all-btn" style="margin-left:8px;">' . esc_html__('Clear all', 'codeweber') . '</button>
                    <span id="cookie-save-message" style="margin-left:8px;"></span>
                </p>
                <textarea id="cookie-export-json" readonly style="display:none; width:100%; height:120px; margin-top:8px; font-family: monospace; font-size: 12px;"></textarea>

                <script>
                    const knownCookies = ' . json_encode($known_cookies, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) . ';
                    const foundCookies = ' . json_encode($found_cookies, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) . ';
                    var cookieScannerI18n = { id: "' . esc_js(__('Identifier', 'codeweber')) . '", value: "' . esc_js(__('Value', 'codeweber')) . '", owner: "' . esc_js(__('Owner', 'codeweber')) . '", storage: "' . esc_js(__('Storage duration', 'codeweber')) . '", type: "' . esc_js(__('Cookie type', 'codeweber')) . '", necessary: "' . esc_js(__('Necessary', 'codeweber')) . '", analytics: "' . esc_js(__('Analytics', 'codeweber')) . '", marketing: "' . esc_js(__('Marketing', 'codeweber')) . '", functional: "' . esc_js(__('Functional', 'codeweber')) . '", other: "' . esc_js(__('Other', 'codeweber')) . '", delete: "' . esc_js(__('Delete', 'codeweber')) . '", clearAll: "' . esc_js(__('Clear all', 'codeweber')) . '" };
                    var cookieScannerDeletedKeys = [];
                    var cookieScannerAjaxUrl = "' . esc_js(admin_url('admin-ajax.php')) . '";
                    var cookieScannerSaveNonce = "' . esc_js(wp_create_nonce('codeweber_save_known_cookies')) . '";
                    var cookieScannerAdminBlocklist = ' . json_encode(array_values($cookie_scanner_admin_blocklist)) . ';

                    function cookieScannerEsc(s) {
                        if (s == null) return "";
                        return String(s).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/\'/g, "&#39;");
                    }
                    function cookieScannerParseList(cookieStr) {
                        var raw = (cookieStr || "").trim();
                        if (!raw) return [];
                        return raw.split(";").map(function(part) {
                            part = part.trim();
                            var idx = part.indexOf("=");
                            if (idx < 0) return { name: part, value: "" };
                            return { name: part.slice(0, idx).trim(), value: part.slice(idx + 1).trim() };
                        }).filter(function(p) { return p.name !== ""; });
                    }
                    function cookieScannerDecodeValue(str) {
                        try { return decodeURIComponent(str || ""); } catch (e) { return str || ""; }
                    }
                    var typeMap = { necessary: "necessary", analytics: "analytics", marketing: "marketing", functional: "functional", other: "other", "Необходимые": "necessary", "Аналитические": "analytics", "Маркетинговые": "marketing", "Функциональные": "functional", "Other": "other" };

                    function cookieScannerRenderTable(list) {
                        cookieScannerDeletedKeys = [];
                        var html = "<table style=\\"width:100%; min-width:100%; border-collapse:collapse;\\"><thead><tr>" +
                            "<th style=\\"border:1px solid #ddd;padding:8px;text-align:left;white-space:nowrap;\\">" + cookieScannerEsc(cookieScannerI18n.id) + "</th>" +
                            "<th style=\\"border:1px solid #ddd;padding:8px;text-align:left;white-space:nowrap;\\">" + cookieScannerEsc(cookieScannerI18n.value) + "</th>" +
                            "<th style=\\"border:1px solid #ddd;padding:8px;text-align:left;white-space:nowrap;min-width:80px;\\">" + cookieScannerEsc(cookieScannerI18n.owner) + "</th>" +
                            "<th style=\\"border:1px solid #ddd;padding:8px;text-align:left;white-space:nowrap;min-width:120px;\\">" + cookieScannerEsc(cookieScannerI18n.storage) + "</th>" +
                            "<th style=\\"border:1px solid #ddd;padding:8px;text-align:left;white-space:nowrap;min-width:80px;\\">" + cookieScannerEsc(cookieScannerI18n.type) + "</th>" +
                            "<th style=\\"border:1px solid #ddd;padding:8px;text-align:center;white-space:nowrap;width:70px;\\"></th></tr></thead><tbody>";
                        list.forEach(function(item) {
                            var info = knownCookies[item.name] || foundCookies[item.name] || {};
                            var normalizedType = typeMap[info.type] || typeMap[info.type && info.type.trim()] || "other";
                            var valueDisplay = item.value ? cookieScannerDecodeValue(item.value) : "";
                            html += "<tr><td style=\\"border:1px solid #ddd;padding:8px;\\">" + cookieScannerEsc(item.name) + "</td>" +
                                "<td style=\\"border:1px solid #ddd;padding:8px;\\">" + cookieScannerEsc(valueDisplay) + "</td>" +
                                "<td style=\\"border:1px solid #ddd;padding:8px;min-width:200px;\\"><input type=\'text\' value=\'" + cookieScannerEsc(info.description || info.owner || "") + "\' placeholder=\'" + cookieScannerEsc(cookieScannerI18n.owner) + "\' style=\'width:100%;min-width:180px;box-sizing:border-box;\'></td>" +
                                "<td style=\\"border:1px solid #ddd;padding:8px;\\"><input type=\'text\' value=\'" + cookieScannerEsc(info.storage_duration || "") + "\' placeholder=\'" + cookieScannerEsc(cookieScannerI18n.storage) + "\' style=\'width:auto;min-width:100px;box-sizing:border-box;\'></td>" +
                                "<td style=\\"border:1px solid #ddd;padding:8px;\\"><select style=\'width:auto;min-width:70px;box-sizing:border-box;\'>" +
                                    "<option value=\'necessary\' " + (normalizedType === "necessary" ? "selected" : "") + ">" + cookieScannerEsc(cookieScannerI18n.necessary) + "</option>" +
                                    "<option value=\'analytics\' " + (normalizedType === "analytics" ? "selected" : "") + ">" + cookieScannerEsc(cookieScannerI18n.analytics) + "</option>" +
                                    "<option value=\'marketing\' " + (normalizedType === "marketing" ? "selected" : "") + ">" + cookieScannerEsc(cookieScannerI18n.marketing) + "</option>" +
                                    "<option value=\'functional\' " + (normalizedType === "functional" ? "selected" : "") + ">" + cookieScannerEsc(cookieScannerI18n.functional) + "</option>" +
                                    "<option value=\'other\' " + (normalizedType === "other" ? "selected" : "") + ">" + cookieScannerEsc(cookieScannerI18n.other) + "</option></select></td>" +
                                "<td style=\\"border:1px solid #ddd;padding:8px;text-align:center;\\"><button type=\'button\' class=\'button button-small cookie-row-delete\' data-name=\'" + cookieScannerEsc(item.name) + "\'>" + cookieScannerEsc(cookieScannerI18n.delete) + "</button></td></tr>";
                        });
                        html += "</tbody></table>";
                        document.getElementById("cookie-frontend-results").innerHTML = html;
                        document.getElementById("cookie-export-actions").style.display = "block";
                        document.getElementById("cookie-export-json").style.display = "none";
                        document.getElementById("cookie-save-message").textContent = "";
                        document.getElementById("cookie-frontend-results").querySelectorAll(".cookie-row-delete").forEach(function(btn) {
                            btn.addEventListener("click", function() {
                                var name = btn.getAttribute("data-name");
                                if (name) cookieScannerDeletedKeys.push(name);
                                var tr = btn.closest("tr");
                                if (tr) tr.parentNode.removeChild(tr);
                            });
                        });
                    }

                    (function loadSavedCookiesTable() {
                        var names = Object.keys(foundCookies);
                        if (names.length > 0) {
                            var list = names.map(function(name) { return { name: name, value: "" }; });
                            cookieScannerRenderTable(list);
                        }
                    })();

                    document.getElementById("scan-frontend-cookies-btn").addEventListener("click", function() {
                        var u = (document.getElementById("cookie-scan-url").value || "").trim() || "/";
                        if (u.indexOf("http") !== 0) u = window.location.origin + (u.indexOf("/") === 0 ? u : "/" + u);
                        var popup = window.open(u, "cookieScanner", "width=800,height=600");
                        window.addEventListener("message", function(event) {
                            if (event.origin !== window.location.origin) return;
                            if (!event.data || event.data.type !== "frontend_cookies") return;

                            var list = cookieScannerParseList(event.data.cookies);
                            list = list.filter(function(item) {
                                return !cookieScannerAdminBlocklist.some(function(prefix) { return item.name.indexOf(prefix) === 0; });
                            });
                            cookieScannerRenderTable(list);
                            if (popup && !popup.closed) popup.close();
                        }, { once: true });
                    });

                    function cookieScannerCollectTable() {
                        var out = {};
                        var rows = document.querySelectorAll("#cookie-frontend-results tbody tr");
                        rows.forEach(function(tr) {
                            var name = (tr.cells[0] && tr.cells[0].textContent) ? tr.cells[0].textContent.trim() : "";
                            if (!name) return;
                            var inputs = tr.querySelectorAll("input[type=text]");
                            var sel = tr.querySelector("select");
                            out[name] = {
                                owner: inputs[0] ? inputs[0].value.trim() : "",
                                storage_duration: inputs[1] ? inputs[1].value.trim() : "",
                                type: sel ? sel.value : "other"
                            };
                        });
                        return out;
                    }

                    document.getElementById("cookie-export-json-btn").addEventListener("click", function() {
                        var data = cookieScannerCollectTable();
                        var json = JSON.stringify(data, null, 2);
                        var ta = document.getElementById("cookie-export-json");
                        ta.value = json;
                        ta.style.display = "block";
                        ta.select();
                        try { document.execCommand("copy"); } catch (e) {}
                    });

                    document.getElementById("cookie-save-file-btn").addEventListener("click", function() {
                        var data = cookieScannerCollectTable();
                        var msgEl = document.getElementById("cookie-save-message");
                        var btn = document.getElementById("cookie-save-file-btn");
                        btn.disabled = true;
                        msgEl.textContent = "";
                        var formData = new FormData();
                        formData.append("action", "codeweber_save_known_cookies");
                        formData.append("nonce", cookieScannerSaveNonce);
                        formData.append("cookies_json", JSON.stringify(data));
                        formData.append("deleted_keys", JSON.stringify(cookieScannerDeletedKeys));
                        fetch(cookieScannerAjaxUrl, { method: "POST", body: formData, credentials: "same-origin" })
                            .then(function(r) { return r.json(); })
                            .then(function(res) {
                                if (res.success) {
                                    cookieScannerDeletedKeys = [];
                                    msgEl.style.color = "green";
                                    msgEl.textContent = res.data && res.data.message ? res.data.message : "Saved.";
                                } else {
                                    msgEl.style.color = "#b32d2e";
                                    msgEl.textContent = res.data && res.data.message ? res.data.message : "Error.";
                                }
                            })
                            .catch(function() {
                                msgEl.style.color = "#b32d2e";
                                msgEl.textContent = "Request failed.";
                            })
                            .then(function() { btn.disabled = false; });
                    });

                    document.getElementById("cookie-clear-all-btn").addEventListener("click", function() {
                        if (!confirm(cookieScannerI18n.clearAll + "?")) return;
                        var btn = document.getElementById("cookie-clear-all-btn");
                        btn.disabled = true;
                        var formData = new FormData();
                        formData.append("action", "codeweber_save_known_cookies");
                        formData.append("nonce", cookieScannerSaveNonce);
                        formData.append("cookies_json", "{}");
                        formData.append("replace_entirely", "1");
                        fetch(cookieScannerAjaxUrl, { method: "POST", body: formData, credentials: "same-origin" })
                            .then(function(r) { return r.json(); })
                            .then(function(res) {
                                if (res.success) {
                                    document.getElementById("cookie-frontend-results").innerHTML = "";
                                    document.getElementById("cookie-export-actions").style.display = "none";
                                    document.getElementById("cookie-save-message").textContent = res.data && res.data.message ? res.data.message : "";
                                    document.getElementById("cookie-save-message").style.color = "green";
                                    cookieScannerDeletedKeys = [];
                                }
                            })
                            .then(function() { btn.disabled = false; });
                    });
                </script>
            ',
        ],
    ],
]);
