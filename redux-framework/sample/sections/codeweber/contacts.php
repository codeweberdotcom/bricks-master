<?php

Redux::set_section(
	$opt_name,
	array(
		'title'            => esc_html__("Contacts", "codeweber"),
		'id'               => 'contacts',
		'desc'             => esc_html__("Settings Contacts", "codeweber"),
		'customizer_width' => '300px',
		'icon'             => 'el el-home',
		'fields'           => array(
			array(
				'id'       => 'e-mail',
				'type'     => 'text',
				'title'    => esc_html__('E-Mail', 'codeweber'),
				'desc'    => '[get_contact field="e-mail"]<br>
[get_contact field="e-mail" type="link"]<br>
[get_contact field="e-mail" type="link" class="phone-link"]',
				'default' => 'test@mail.com',
			),
			array(
				'id'       => 'phone_01',
				'type'     => 'text',
				'title'    => esc_html__('Phone 01', 'codeweber'),
				'subtitle' => esc_html__('Format +7(495)XXX-XX-XX, 8(800)XXX-XX-XX, XXX', 'codeweber'),
				'desc'    => '[get_contact field="phone_01"]<br>[get_contact field="phone_01" type="link"]<br>[get_contact field="phone_01" type="link" class="phone-link"]',
				'default' => '+7(495)000-00-00',

			),
			array(
				'id'       => 'phone_02',
				'type'     => 'text',
				'title'    => esc_html__('Phone 02', 'codeweber'),
				'subtitle' => esc_html__('Format +7(495)XXX-XX-XX, 8(800)XXX-XX-XX, XXX', 'codeweber'),
				'desc'    => '[get_contact field="phone_02"]<br>[get_contact field="phone_02" type="link"]<br>[get_contact field="phone_02" type="link" class="phone-link"]',
				'default' => '+7(495)000-00-00',
			),
			array(
				'id'       => 'phone_03',
				'type'     => 'text',
				'title'    => esc_html__('Phone 03', 'codeweber'),
				'subtitle' => esc_html__('Format +7(495)XXX-XX-XX, 8(800)XXX-XX-XX, XXX', 'codeweber'),
				'desc'    => '[get_contact field="phone_03"]<br>[get_contact field="phone_03" type="link"]<br>[get_contact field="phone_03" type="link" class="phone-link"]',
				'default' => '+7(495)000-00-00',
			),
			array(
				'id'       => 'phone_04',
				'type'     => 'text',
				'title'    => esc_html__('Phone 04', 'codeweber'),
				'subtitle' => esc_html__('Format +7(495)XXX-XX-XX, 8(800)XXX-XX-XX, XXX', 'codeweber'),
				'desc'    => '[get_contact field="phone_04"]<br>[get_contact field="phone_04" type="link"]<br>[get_contact field="phone_04" type="link" class="phone-link"]',
				'default' => '+7(495)000-00-00',
			),
			array(
				'id'       => 'phone_05',
				'type'     => 'text',
				'title'    => esc_html__('Phone 05', 'codeweber'),
				'subtitle' => esc_html__('Format +7(495)XXX-XX-XX, 8(800)XXX-XX-XX, XXX', 'codeweber'),
				'desc'    => '[get_contact field="phone_05"]<br>[get_contact field="phone_05" type="link"]<br>[get_contact field="phone_05" type="link" class="phone-link"]',
				'default' => '+7(495)000-00-00',
			),

		),
	)
);
