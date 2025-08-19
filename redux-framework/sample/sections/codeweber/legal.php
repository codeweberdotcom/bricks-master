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
			'title'    => esc_html__('Основные данные', 'codeweber'),
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
				'title'   => __('Сookie Expiration Date', 'codeweber'),
				'default' => __('365', 'codeweber'),
			),

			array(
				'id'      => 'welcome_text_cookie_banneer',
				'type'    => 'editor',
				'args'   => array(
					'teeny'            => true,
					'textarea_rows'    => 10
				),

				'title'   => __('Welcome Text Cookie Banner', 'codeweber'),
				'default' => __('На данном сайте используются файлы «cookies» для обеспечения его бесперебойной работы и для запоминания выбранных настроек, а также инструменты аналитики (Яндекс.Метрика). Продолжая работу с сайтом, Вы подтверждаете свое согласие на обработку и использование «cookies» Вашего браузера в соответствии с <a href="[url_cookie-policy]")>Политикой в отношении использования файлов Куки(Сookie)</a>', 'codeweber'),
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

// Загружаем файл JSON с куками, если есть
$known_cookies_file = get_template_directory() . '/components/cookies-known.json';
$known_cookies = [];
if (file_exists($known_cookies_file)) {
    $json_content = file_get_contents($known_cookies_file);
    $known_cookies = json_decode($json_content, true);
    if (!is_array($known_cookies)) {
        $known_cookies = []; // на случай ошибок парсинга JSON
    }
}

// Код внутри Redux::set_section
Redux::set_section($opt_name, [
    'title'      => __('Cookie Scanner', 'codeweber'),
    'id'         => 'cookie_scanner',
    'desc'       => __('Scan and display browser cookies.', 'codeweber'),
    'subsection' => true,
    'fields'     => [
        [
            'id'      => 'cookie_scan_raw',
            'type'    => 'raw',
            'title'   => __('Scan Frontend Cookies', 'codeweber'),
            'content' => '
                <button type="button" class="button button-primary" id="scan-frontend-cookies-btn">' .
                    __('Scan Frontend Cookies', 'codeweber') .
                '</button>
                <div id="cookie-frontend-results" style="margin-top:15px; max-height: 300px; overflow-y: auto; font-family: monospace;"></div>

                <script>
                    // Передаем PHP-массив known_cookies в JS в виде объекта
                    const knownCookies = ' . json_encode($known_cookies, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) . ';

                    document.getElementById("scan-frontend-cookies-btn").addEventListener("click", function() {
                        const popup = window.open("/", "cookieScanner", "width=800,height=600");

                        window.addEventListener("message", function(event) {
                            if (event.origin !== window.location.origin) return;
                            if (!event.data || event.data.type !== "frontend_cookies") return;

                            const cookies = event.data.cookies.split(";").map(c => c.trim().split("="));
                            let html = "<table style=\\"border-collapse:collapse;\\">";
                            html += "<thead><tr>" +
                                "<th style=\\"border:1px solid #ddd;padding:8px;text-align:left;white-space:nowrap;\\">Идентификатор</th>" +
                                "<th style=\\"border:1px solid #ddd;padding:8px;text-align:left;white-space:nowrap;\\">Значение</th>" +
                                "<th style=\\"border:1px solid #ddd;padding:8px;text-align:left;white-space:nowrap;min-width:80px;\\">Владелец</th>" +
                                "<th style=\\"border:1px solid #ddd;padding:8px;text-align:left;white-space:nowrap;min-width:120px;\\">Время хранения Куки</th>" +
                                "<th style=\\"border:1px solid #ddd;padding:8px;text-align:left;white-space:nowrap;min-width:80px;\\">Тип Куки</th>" +
                                "</tr></thead><tbody>";

                            cookies.forEach(function(pair) {
                                const name = pair[0];
                                const value = decodeURIComponent(pair[1] || "");
                                const info = knownCookies[name] || {};

                                html += "<tr>" +
                                    "<td style=\\"border:1px solid #ddd;padding:8px;\\">" + name + "</td>" +
                                    "<td style=\\"border:1px solid #ddd;padding:8px;\\">" + value + "</td>" +
                                    "<td style=\\"border:1px solid #ddd;padding:8px;\\"><input type=\'text\' value=\'" + (info.owner || "") + "\' placeholder=\'Owner\' style=\'width:auto; min-width: 70px; box-sizing: border-box;\'></td>" +
                                    "<td style=\\"border:1px solid #ddd;padding:8px;\\"><input type=\'text\' value=\'" + (info.storage_duration || "") + "\' placeholder=\'Storage Duration\' style=\'width:auto; min-width: 100px; box-sizing: border-box;\'></td>" +
                                    "<td style=\\"border:1px solid #ddd;padding:8px;\\"><select style=\'width:auto; min-width: 70px; box-sizing: border-box;\'>" +
                                        "<option value=\'necessary\' " + ((info.type === "necessary") ? "selected" : "") + ">Необходимые</option>" +
                                        "<option value=\'analytics\' " + ((info.type === "analytics") ? "selected" : "") + ">Аналитические</option>" +
                                        "<option value=\'marketing\' " + ((info.type === "marketing") ? "selected" : "") + ">Маркетинговые</option>" +
                                        "<option value=\'functional\' " + ((info.type === "functional") ? "selected" : "") + ">Функциональные</option>" +
                                        "<option value=\'other\' " + ((info.type === "other") ? "selected" : "") + ">Other</option>" +
                                    "</select></td>" +
                                    "</tr>";
                            });

                            html += "</tbody></table>";
                            document.getElementById("cookie-frontend-results").innerHTML = html;

							// --- Добавлено закрытие окна без ломания кода ---
    if (popup && !popup.closed) {
        popup.close();
    }

                        }, { once: true });
                    });
                </script>
            ',
        ],
    ],
]);
