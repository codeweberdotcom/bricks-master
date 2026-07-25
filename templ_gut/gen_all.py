#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Batch Gutenberg generator.
For each JSON in json/, substitutes the barbershop template data with the JSON data.
Outputs: gutenberg_ready_code/{name}.txt
"""

import json
import os
import glob

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
TEMPLATE_PATH = os.path.join(BASE_DIR, 'gutenberg_template.txt')
JSON_DIR = os.path.join(BASE_DIR, 'json')
OUTPUT_DIR = os.path.join(BASE_DIR, 'gutenberg_ready_code')

# ─────────────────────────────────────────────
# Exact source strings from the barbershop template
# ─────────────────────────────────────────────

SRC_HERO_EYEBROW = 'Барбершоп Борода '
SRC_HERO_TITLE_0 = 'Стрижка и бритьё'
SRC_HERO_TITLE_1 = 'по высшему разряду'
SRC_HERO_LEAD    = ('Опытные барберы, премиальная косметика и атмосфера '
                    'настоящего мужского клуба. Запишись онлайн за 1 минуту.')

SRC_SERVICES = [
    ('Мужская стрижка',      'Подбор формы под тип лица и стиль.'),
    ('Моделирование бороды', 'Стрижка, оформление и уход.'),
    ('Королевское бритьё',   'Опасная бритва и горячее полотенце.'),
    ('Камуфляж седины',      'Натуральный результат за 15 минут.'),
    ('Уход за лицом',        'Чистка, маски и увлажнение кожи.'),
    ('Укладка',              'Стайлинг под образ и тип волос.'),
]

SRC_ADVANTAGE_0_TITLE = 'Опытные специалисты'
SRC_ADVANTAGE_0_TEXT  = 'Специалисты со стажем и постоянным обучением новым техникам '

SRC_ABOUT_NAME = 'BARBER'

SRC_STATS = [
    ('8 лет',    'на рынке'),
    ('12 000+',  'Клиентов'),
    ('4.9',      'Рейтинг на Yandex'),
    ('6',        'Мастеров'),
]

SRC_PRICING = [
    {
        'group': 'Стрижка и борода',
        'items': [
            ('Мужская стрижка',      '1 500 ₽'),
            ('Стрижка машинкой',     '1 000 ₽'),
            ('Моделирование бороды', '1 200 ₽'),
            ('Стрижка + борода',     '2 500 ₽'),
        ],
    },
    {
        'group': 'Бритьё и уход',
        'items': [
            ('Королевское бритьё', '1 400 ₽'),
            ('Камуфляж седины',    '900 ₽'),
            ('Уход за лицом',      '1 100 ₽'),
            ('Укладка',            '500 ₽'),
        ],
    },
]

# Template team order: Сотрудник 1..4
# Each entry: (name, role, h3_class, div_class)
SRC_TEAM = [
    ('Ирина Брейк',   'Барбер',         'text-soft-dark text-left h4', 'text-left text-uppercase mb-1 meta'),
    ('Сергей Белов',  'Мастер бритья',   'text-left h4',               'text-left mb-1 meta'),
    ('Дмитрий Орлов', 'Барбер-стилист',  'text-left h4',               'text-left mb-1 meta'),
    ('Артём Волков',  'Топ-барбер',      'text-left h4',               'text-left mb-1 meta'),
]

SRC_WORKS_EYEBROW = 'Наше портфолио'


# ─────────────────────────────────────────────
# Helpers
# ─────────────────────────────────────────────

def pricing_rows_json(items):
    cells = [
        '{{"cells":[{{"content":"{n}","colspan":1,"rowspan":1}},{{"content":"{p}","colspan":1,"rowspan":1}}]}}'.format(n=name, p=price)
        for name, price in items
    ]
    return '[' + ','.join(cells) + ']'


def pricing_tbody_html(items):
    rows = []
    last = len(items) - 1
    for i, (name, price) in enumerate(items):
        if i == 0 and i == last:
            th = f'<th scope="row" class="border-top-0 border-bottom-0">{name}</th>'
            td = f'<td class="text-end border-top-0 border-bottom-0">{price}</td>'
        elif i == 0:
            th = f'<th scope="row" class="border-top-0">{name}</th>'
            td = f'<td class="text-end border-top-0">{price}</td>'
        elif i == last:
            th = f'<th scope="row" class="border-bottom-0">{name}</th>'
            td = f'<td class="text-end border-bottom-0">{price}</td>'
        else:
            th = f'<th scope="row">{name}</th>'
            td = f'<td class="text-end">{price}</td>'
        rows.append(f'<tr>{th}{td}</tr>')
    return '<tbody style="border-top:0">' + ''.join(rows) + '</tbody>'


# ─────────────────────────────────────────────
# Core: build replacement list for one JSON
# ─────────────────────────────────────────────

def build_replacements(data):
    s = data['site']
    c = data['common']
    reps = []   # list of (src_str, tgt_str)

    # 1. Hero eyebrow
    eyebrow = s['hero']['eyebrow']
    reps.append((f'"subtitle":"{SRC_HERO_EYEBROW}"', f'"subtitle":"{eyebrow}"'))
    reps.append((f'>{SRC_HERO_EYEBROW}</div>', f'>{eyebrow}</div>'))

    # 2. Hero title (both parts appear as plain UTF-8 in JSON unicode-escape
    #    and directly in HTML — single replace covers both)
    reps.append((SRC_HERO_TITLE_0, s['hero']['title'][0]))
    reps.append((SRC_HERO_TITLE_1, s['hero']['title'][1]))

    # 3. Hero lead (plain text, appears twice: JSON "text" value and HTML <p>)
    reps.append((SRC_HERO_LEAD, s['hero']['lead']))

    # 4. Services (6 items)
    services = s['services']
    for i, (src_t, src_p) in enumerate(SRC_SERVICES):
        tgt_t = services[i]['title']
        tgt_p = services[i]['text']
        # JSON — title context: titleSize:h5 distinguishes from advantages
        reps.append((
            f'"title":"{src_t}","titleColor":"dark","titleSize":"h5"',
            f'"title":"{tgt_t}","titleColor":"dark","titleSize":"h5"',
        ))
        # JSON — paragraph with paragraphClass:mb-0 context (services only)
        reps.append((
            f'"paragraph":"{src_p}","paragraphClass":"mb-0"',
            f'"paragraph":"{tgt_p}","paragraphClass":"mb-0"',
        ))
        # HTML — h3 + p as a unit
        reps.append((
            f'<h3 class="text-dark h5 mb-1">{src_t}</h3><p class="mb-0">{src_p}</p>',
            f'<h3 class="text-dark h5 mb-1">{tgt_t}</h3><p class="mb-0">{tgt_p}</p>',
        ))

    # 5. Advantage item 0 (title varies per niche: "Опытные барберы/мастера/...")
    adv0 = c['advantages']['items'][0]
    tgt_adv_t = adv0['title']
    tgt_adv_p = adv0['text']
    reps.append((
        f'"title":"{SRC_ADVANTAGE_0_TITLE}","titleColor":"dark","paragraph":"{SRC_ADVANTAGE_0_TEXT}"',
        f'"title":"{tgt_adv_t}","titleColor":"dark","paragraph":"{tgt_adv_p}"',
    ))
    reps.append((
        f'<h3 class="text-dark">{SRC_ADVANTAGE_0_TITLE}</h3>'
        f'<p class="mb-3">{SRC_ADVANTAGE_0_TEXT}</p>',
        f'<h3 class="text-dark">{tgt_adv_t}</h3>'
        f'<p class="mb-3">{tgt_adv_p}</p>',
    ))

    # 6. About name (2 occurrences: JSON "text" value and HTML <p>)
    name = s['name']
    reps.append((
        f'{SRC_ABOUT_NAME} — это команда',
        f'{name} — это команда',
    ))

    # 7. Stats (4 items) — counter blocks
    stats = s['stats']
    for i, (src_v, src_l) in enumerate(SRC_STATS):
        tgt_v = stats[i]['value']
        tgt_l = stats[i]['label']
        reps.append((
            f'"title":"{src_v}","counterLg":true,"paragraph":"{src_l}"',
            f'"title":"{tgt_v}","counterLg":true,"paragraph":"{tgt_l}"',
        ))
        reps.append((
            f'<h3 class="counter counter-lg text-white text-center mb-1">{src_v}</h3>'
            f'<p class="mb-0">{src_l}</p>',
            f'<h3 class="counter counter-lg text-white text-center mb-1">{tgt_v}</h3>'
            f'<p class="mb-0">{tgt_l}</p>',
        ))

    # 8. Pricing cards (2 cards × 4 rows)
    pricing = s['pricing']
    for ci, src_card in enumerate(SRC_PRICING):
        tgt_card = pricing[ci]
        src_g = src_card['group']
        tgt_g = tgt_card['group']

        # Card heading
        reps.append((
            f'"enableSubtitle":false,"title":"{src_g}"}}',
            f'"enableSubtitle":false,"title":"{tgt_g}"}}',
        ))
        reps.append((
            f'<h2 class="text-left">{src_g}</h2>',
            f'<h2 class="text-left">{tgt_g}</h2>',
        ))

        # Rows — replace entire JSON rows array and entire HTML tbody at once
        tgt_items = [(item['name'], item['price']) for item in tgt_card['items']]
        reps.append((
            f'"rows":{pricing_rows_json(src_card["items"])}',
            f'"rows":{pricing_rows_json(tgt_items)}',
        ))
        reps.append((
            pricing_tbody_html(src_card['items']),
            pricing_tbody_html(tgt_items),
        ))

    # 9. Team members (4 — matched by template position, not by name)
    team = s['team']
    for i, (src_n, src_r, h3_cls, div_cls) in enumerate(SRC_TEAM):
        tgt_n = team[i]['name']
        tgt_r = team[i]['role']
        reps.append((
            f'"title":"{src_n}","subtitle":"{src_r}","order":"title-first"',
            f'"title":"{tgt_n}","subtitle":"{tgt_r}","order":"title-first"',
        ))
        reps.append((
            f'<h3 class="{h3_cls}">{src_n}</h3>'
            f'<div class="{div_cls}">{src_r}</div>',
            f'<h3 class="{h3_cls}">{tgt_n}</h3>'
            f'<div class="{div_cls}">{tgt_r}</div>',
        ))

    # 10. Works section eyebrow
    works_eyebrow = c['sectionHeads']['works']['eyebrow']
    reps.append((f'"subtitle":"{SRC_WORKS_EYEBROW}"', f'"subtitle":"{works_eyebrow}"'))
    reps.append((f'>{SRC_WORKS_EYEBROW}</div>', f'>{works_eyebrow}</div>'))

    return reps


def generate(template, data):
    t = template
    for src_str, tgt_str in build_replacements(data):
        if src_str != tgt_str:
            t = t.replace(src_str, tgt_str)
    return t


# ─────────────────────────────────────────────
# Verification helpers
# ─────────────────────────────────────────────

# Strings that are unique to the TEMPLATE and should be replaced in every output.
# Only list strings that NO target JSON would legitimately reproduce.
TEMPLATE_ONLY_MARKERS = [
    'Барбершоп Борода ',   # unique barbershop name used only in this template
    'по высшему разряду',  # unique phrasing of the barbershop hero title
    'Рейтинг на Yandex',   # exact stat label from this template
    'Ирина Брейк',         # team member name unique to this template
    'Наше портфолио',      # old works eyebrow in this template
]


def verify(result, data, basename):
    issues = []
    for marker in TEMPLATE_ONLY_MARKERS:
        if marker in result:
            issues.append(f'leftover: "{marker}"')
    name = data['site']['name']
    if name not in result:
        issues.append(f'missing site name: "{name}"')
    return issues


# ─────────────────────────────────────────────
# Main
# ─────────────────────────────────────────────

def main():
    print(f'Reading template …')
    with open(TEMPLATE_PATH, 'r', encoding='utf-8') as f:
        template = f.read()
    print(f'  Template size: {len(template):,} chars')

    os.makedirs(OUTPUT_DIR, exist_ok=True)

    json_files = sorted(glob.glob(os.path.join(JSON_DIR, '*.json')))
    print(f'Found {len(json_files)} JSON files\n')

    ok = 0
    warn = 0
    errors = []

    for json_path in json_files:
        basename = os.path.splitext(os.path.basename(json_path))[0]
        out_path = os.path.join(OUTPUT_DIR, f'{basename}.txt')

        try:
            with open(json_path, 'r', encoding='utf-8') as f:
                data = json.load(f)

            if 'site' not in data:
                print(f'  SKIP  {basename} (no site key)')
                continue

            result = generate(template, data)

            with open(out_path, 'w', encoding='utf-8') as f:
                f.write(result)

            issues = verify(result, data, basename)
            if issues:
                warn += 1
                print(f'  WARN  {basename}.txt - {"; ".join(issues)}')
            else:
                ok += 1
                print(f'  OK    {basename}.txt')

        except Exception as e:
            import traceback
            print(f'  ERR   {basename}: {e}')
            traceback.print_exc()
            errors.append((basename, str(e)))

    print(f'\n{"="*50}')
    print(f'Generated: {ok} OK, {warn} with warnings, {len(errors)} errors')
    if errors:
        for name, err in errors:
            print(f'  - {name}: {err}')


if __name__ == '__main__':
    main()
