/**
 * Design Token Extractor
 * Injected via Playwright browser_evaluate to extract colors, fonts, button styles,
 * form inputs, breadcrumb, and navigation from a page.
 * Returns a JSON object with all extracted design tokens.
 */
(() => {
  const result = {
    url: window.location.href,
    title: document.title,
    colors: {},
    typography: {},
    buttons: {},
    forms: {},
    breadcrumb: {},
    navigation: {},
    meta: {
      extractedAt: new Date().toISOString(),
      viewportWidth: window.innerWidth,
      viewportHeight: window.innerHeight,
    },
  };

  // ── Helpers ──

  function rgbToHex(rgb) {
    if (!rgb || rgb === 'transparent' || rgb === 'rgba(0, 0, 0, 0)') return null;
    const match = rgb.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)/);
    if (!match) return rgb;
    const r = parseInt(match[1]);
    const g = parseInt(match[2]);
    const b = parseInt(match[3]);
    return '#' + [r, g, b].map(x => x.toString(16).padStart(2, '0')).join('');
  }

  function getComputedStyleProp(el, prop) {
    return window.getComputedStyle(el).getPropertyValue(prop).trim();
  }

  function pxToRem(pxValue, rootFontSize) {
    const px = parseFloat(pxValue);
    if (isNaN(px)) return pxValue;
    return (px / rootFontSize).toFixed(3).replace(/\.?0+$/, '') + 'rem';
  }

  // ── 1. Extract Colors ──

  const colorMap = new Map(); // hex → count
  const bgColorMap = new Map();
  const borderColorMap = new Map();

  const allElements = document.querySelectorAll('body *');
  const sampleSize = Math.min(allElements.length, 2000);
  const step = Math.max(1, Math.floor(allElements.length / sampleSize));

  for (let i = 0; i < allElements.length; i += step) {
    const el = allElements[i];
    const style = window.getComputedStyle(el);

    // Text color
    const color = rgbToHex(style.color);
    if (color) colorMap.set(color, (colorMap.get(color) || 0) + 1);

    // Background color
    const bg = rgbToHex(style.backgroundColor);
    if (bg) bgColorMap.set(bg, (bgColorMap.get(bg) || 0) + 1);

    // Border color
    const border = rgbToHex(style.borderTopColor);
    if (border && border !== color) {
      borderColorMap.set(border, (borderColorMap.get(border) || 0) + 1);
    }
  }

  // Sort by frequency and take top colors
  const sortByFreq = (map, limit = 20) =>
    [...map.entries()]
      .sort((a, b) => b[1] - a[1])
      .slice(0, limit)
      .map(([hex, count]) => ({ hex, count }));

  result.colors.text = sortByFreq(colorMap);
  result.colors.background = sortByFreq(bgColorMap);
  result.colors.border = sortByFreq(borderColorMap);

  // Extract link colors
  const links = document.querySelectorAll('a');
  const linkColors = new Map();
  links.forEach(a => {
    const c = rgbToHex(window.getComputedStyle(a).color);
    if (c) linkColors.set(c, (linkColors.get(c) || 0) + 1);
  });
  result.colors.links = sortByFreq(linkColors, 10);

  // CSS custom properties from :root
  const rootStyles = getComputedStyle(document.documentElement);
  const cssVars = {};
  const varNames = [
    '--bs-primary', '--bs-secondary', '--bs-success', '--bs-info',
    '--bs-warning', '--bs-danger', '--bs-light', '--bs-dark',
    '--bs-body-color', '--bs-body-bg', '--bs-link-color',
    '--bs-border-color',
  ];
  varNames.forEach(name => {
    const val = rootStyles.getPropertyValue(name).trim();
    if (val) cssVars[name] = val.startsWith('#') ? val : rgbToHex(val) || val;
  });
  result.colors.cssVariables = cssVars;

  // ── 2. Extract Typography ──

  const rootFontSize = parseFloat(getComputedStyleProp(document.documentElement, 'font-size'));
  result.typography.rootFontSize = rootFontSize + 'px';

  // Body font
  const body = document.body;
  const bodyStyle = window.getComputedStyle(body);
  const bodyLineHeightPx = parseFloat(bodyStyle.lineHeight);
  result.typography.body = {
    fontFamily: bodyStyle.fontFamily,
    fontSize: bodyStyle.fontSize,
    fontSizeRem: pxToRem(bodyStyle.fontSize, rootFontSize),
    fontWeight: bodyStyle.fontWeight,
    lineHeight: bodyStyle.lineHeight,
    lineHeightRatio: isNaN(bodyLineHeightPx) ? null : +(bodyLineHeightPx / parseFloat(bodyStyle.fontSize)).toFixed(3),
    color: rgbToHex(bodyStyle.color),
  };

  // Heading styles (h1-h6)
  result.typography.headings = {};
  for (let i = 1; i <= 6; i++) {
    const heading = document.querySelector(`h${i}`);
    if (heading) {
      const hs = window.getComputedStyle(heading);
      result.typography.headings[`h${i}`] = {
        fontSize: hs.fontSize,
        fontSizeRem: pxToRem(hs.fontSize, rootFontSize),
        fontWeight: hs.fontWeight,
        lineHeight: hs.lineHeight,
        letterSpacing: hs.letterSpacing,
        color: rgbToHex(hs.color),
        fontFamily: hs.fontFamily !== bodyStyle.fontFamily ? hs.fontFamily : null,
      };
    }
  }

  // Detect used font families
  const fontFamilies = new Map();
  for (let i = 0; i < allElements.length; i += step) {
    const ff = window.getComputedStyle(allElements[i]).fontFamily;
    if (ff) fontFamilies.set(ff, (fontFamilies.get(ff) || 0) + 1);
  }
  result.typography.usedFonts = [...fontFamilies.entries()]
    .sort((a, b) => b[1] - a[1])
    .slice(0, 10)
    .map(([family, count]) => ({ family, count }));

  // ── 3. Extract Button Styles ──

  const buttonSelectors = [
    'button', '.btn', 'a.btn', 'input[type="submit"]', 'input[type="button"]',
    '[class*="btn-"]', '[role="button"]',
  ];

  const buttonStyles = new Map();

  buttonSelectors.forEach(sel => {
    document.querySelectorAll(sel).forEach(btn => {
      const bs = window.getComputedStyle(btn);
      const key = [
        rgbToHex(bs.backgroundColor),
        rgbToHex(bs.color),
        bs.borderRadius,
        bs.fontSize,
        bs.paddingTop + ' ' + bs.paddingRight + ' ' + bs.paddingBottom + ' ' + bs.paddingLeft,
      ].join('|');

      if (!buttonStyles.has(key)) {
        const classes = [...btn.classList].filter(c => c.startsWith('btn')).join(' ');
        buttonStyles.set(key, {
          text: btn.textContent.trim().substring(0, 50),
          classes: classes || btn.tagName.toLowerCase(),
          backgroundColor: rgbToHex(bs.backgroundColor),
          color: rgbToHex(bs.color),
          borderColor: rgbToHex(bs.borderTopColor),
          borderWidth: bs.borderTopWidth,
          borderRadius: bs.borderRadius,
          borderRadiusRem: pxToRem(bs.borderRadius, rootFontSize),
          fontSize: bs.fontSize,
          fontSizeRem: pxToRem(bs.fontSize, rootFontSize),
          fontWeight: bs.fontWeight,
          padding: {
            top: bs.paddingTop,
            right: bs.paddingRight,
            bottom: bs.paddingBottom,
            left: bs.paddingLeft,
          },
          paddingRem: {
            y: pxToRem(bs.paddingTop, rootFontSize),
            x: pxToRem(bs.paddingRight, rootFontSize),
          },
          textTransform: bs.textTransform,
          letterSpacing: bs.letterSpacing,
          boxShadow: bs.boxShadow !== 'none' ? bs.boxShadow : null,
        });
      }
    });
  });

  result.buttons.styles = [...buttonStyles.values()];
  result.buttons.count = result.buttons.styles.length;

  // ── 4. Extract Form Input Styles ──

  const inputSelectors = 'input[type="text"], input[type="email"], input[type="tel"], input[type="password"], input:not([type]), textarea, select';
  const inputStyleMap = new Map();

  document.querySelectorAll(inputSelectors).forEach(inp => {
    const rect = inp.getBoundingClientRect();
    if (rect.height <= 0 || rect.height > 200) return;
    const is = window.getComputedStyle(inp);
    const key = [is.fontSize, is.backgroundColor, is.borderTopColor, is.borderTopWidth, is.borderRadius, is.paddingTop, is.paddingLeft].join('|');
    if (!inputStyleMap.has(key)) {
      inputStyleMap.set(key, {
        tag: inp.tagName,
        height: Math.round(rect.height),
        fontSize: is.fontSize,
        fontSizeRem: pxToRem(is.fontSize, rootFontSize),
        fontWeight: is.fontWeight,
        color: rgbToHex(is.color),
        backgroundColor: rgbToHex(is.backgroundColor),
        borderColor: rgbToHex(is.borderTopColor),
        borderWidth: is.borderTopWidth,
        borderRadius: is.borderRadius,
        borderRadiusRem: pxToRem(is.borderRadius, rootFontSize),
        paddingY: is.paddingTop,
        paddingX: is.paddingLeft,
        paddingYrem: pxToRem(is.paddingTop, rootFontSize),
        paddingXrem: pxToRem(is.paddingLeft, rootFontSize),
        lineHeight: is.lineHeight,
      });
    }
  });
  result.forms.styles = [...inputStyleMap.values()];
  result.forms.count = result.forms.styles.length;

  // Focus styles — focus first visible input and compare
  const firstInput = document.querySelector(inputSelectors);
  if (firstInput && firstInput.getBoundingClientRect().height > 0) {
    const blurStyle = window.getComputedStyle(firstInput);
    const blurBg = blurStyle.backgroundColor;
    const blurBorder = blurStyle.borderTopColor;
    const blurShadow = blurStyle.boxShadow;
    firstInput.focus();
    const focusStyle = window.getComputedStyle(firstInput);
    result.forms.focus = {
      backgroundColor: rgbToHex(focusStyle.backgroundColor),
      borderColor: rgbToHex(focusStyle.borderTopColor),
      boxShadow: focusStyle.boxShadow !== 'none' ? focusStyle.boxShadow : null,
      color: rgbToHex(focusStyle.color),
      bgChanged: focusStyle.backgroundColor !== blurBg,
      borderChanged: focusStyle.borderTopColor !== blurBorder,
      shadowChanged: focusStyle.boxShadow !== blurShadow,
    };
    firstInput.blur();
  }

  // ── 5. Extract Breadcrumb ──

  const bcEl = document.querySelector('[class*="breadcrumb"], nav[aria-label="breadcrumb"]');
  if (bcEl) {
    const bcLinks = bcEl.querySelectorAll('a');
    if (bcLinks.length > 0) {
      const linkStyle = window.getComputedStyle(bcLinks[0]);
      result.breadcrumb.linkColor = rgbToHex(linkStyle.color);
      result.breadcrumb.linkFontSize = linkStyle.fontSize;
      result.breadcrumb.linkFontWeight = linkStyle.fontWeight;
    }
    // Divider / separator color
    const allSpans = bcEl.querySelectorAll('span, li');
    allSpans.forEach(sp => {
      const txt = sp.textContent.trim();
      if (['—', '/', '>', '»', '·', '|'].includes(txt)) {
        result.breadcrumb.dividerColor = rgbToHex(window.getComputedStyle(sp).color);
        result.breadcrumb.divider = txt;
      }
    });
    // Active (last) item
    const lastItem = bcEl.querySelector('li:last-child, span:last-child');
    if (lastItem && !lastItem.querySelector('a')) {
      result.breadcrumb.activeColor = rgbToHex(window.getComputedStyle(lastItem).color);
    }
  }

  // ── 6. Extract Navigation ──

  const navEl = document.querySelector('nav, [class*="navbar"], [class*="main-nav"], [class*="header-menu"]');
  if (navEl) {
    const navLinks = navEl.querySelectorAll('a');
    if (navLinks.length > 0) {
      const ns = window.getComputedStyle(navLinks[0]);
      result.navigation = {
        fontSize: ns.fontSize,
        fontSizeRem: pxToRem(ns.fontSize, rootFontSize),
        fontWeight: ns.fontWeight,
        color: rgbToHex(ns.color),
        textTransform: ns.textTransform,
        letterSpacing: ns.letterSpacing,
      };
    }
  }

  return result;
})();
