'use strict';

/**
 * Gulpfile для темы Codeweber
 * 
 * Поддержка дочерних тем:
 * - Запускается из родительской темы (codeweber)
 * - Автоматически определяет активную дочернюю тему через PHP скрипт (functions/gulp-theme-info.php)
 * - Генерирует dist в активной дочерней теме (если она есть)
 * - Если дочерняя тема не активна, генерирует dist в родительской теме
 * - Всегда использует src из родительской темы для компиляции
 * - При подключении файлов в WordPress сначала проверяется дочерняя тема,
 *   затем родительская (см. functions/enqueues.php)
 * 
 * Использование:
 * - Запускайте gulp из директории родительской темы: cd wp-content/themes/codeweber && gulp build:dist
 * - Gulp автоматически определит активную дочернюю тему через PHP скрипт
 * - Требуется PHP и доступ к WordPress (wp-load.php)
 */

/* Include fs and child_process for file operations and PHP execution */
var fs = require('fs'),
    path_module = require('path'),
    { execSync } = require('child_process');

/* Get active child theme from WordPress */
function getActiveChildTheme() {
  var parentThemePath = process.cwd();
  var phpScriptPath = path_module.join(parentThemePath, 'functions', 'gulp-theme-info.php');
  
  // Работаем ТОЛЬКО через PHP скрипт - он точно знает, какая тема активна
  if (!fs.existsSync(phpScriptPath)) {
    console.error('❌ ОШИБКА: PHP скрипт не найден: ' + phpScriptPath);
    process.exit(1);
  }
  
  try {
    // Находим PHP
    var phpCommand = 'php';
    if (process.platform === 'win32') {
      // На Windows пробуем разные варианты
      var possiblePhpPaths = [
        'php',
        'C:\\laragon\\bin\\php\\php-8.1.10-Win32-vs16-x64\\php.exe',
        'C:\\laragon\\bin\\php\\php-8.2.12-Win32-vs16-x64\\php.exe',
        'C:\\laragon\\bin\\php\\php-8.3.0-Win32-vs16-x64\\php.exe',
        'C:\\xampp\\php\\php.exe',
        process.env.PHP_BIN || 'php'
      ];
      for (var i = 0; i < possiblePhpPaths.length; i++) {
        try {
          execSync(possiblePhpPaths[i] + ' --version', { stdio: 'ignore' });
          phpCommand = possiblePhpPaths[i];
          break;
        } catch (e) {
          // Пробуем следующий путь
        }
      }
    }
    
    // Выполняем PHP скрипт
    // Игнорируем stderr, чтобы предупреждения не попадали в вывод
    var result = execSync(phpCommand + ' "' + phpScriptPath + '"', { 
      encoding: 'utf8',
      cwd: parentThemePath,
      stdio: ['ignore', 'pipe', 'ignore'], // Игнорируем stdin и stderr, получаем только stdout
      maxBuffer: 1024 * 1024 // Увеличиваем буфер для больших выводов
    });
    
    var resultText = result.trim();
    if (!resultText) {
      console.error('❌ ОШИБКА: PHP скрипт не вернул данные');
      process.exit(1);
    }
    
    try {
      var themeInfo = JSON.parse(resultText);
      // Используем результат PHP скрипта - он точно знает, какая тема активна
      return themeInfo;
    } catch (parseError) {
      console.error('❌ ОШИБКА: Не удалось распарсить JSON от PHP скрипта:');
      console.error('Результат:', resultText);
      console.error('Ошибка:', parseError.message);
      process.exit(1);
    }
  } catch (e) {
    console.error('❌ ОШИБКА: Не удалось выполнить PHP скрипт:');
    console.error('Команда:', phpCommand + ' "' + phpScriptPath + '"');
    console.error('Ошибка:', e.message);
    if (e.stderr) {
      console.error('Stderr:', e.stderr.toString());
    }
    if (e.stdout) {
      console.error('Stdout:', e.stdout.toString());
    }
    console.error('\nУбедитесь, что:');
    console.error('1. PHP установлен и доступен в PATH');
    console.error('2. WordPress установлен и доступен');
    console.error('3. PHP скрипт может загрузить wp-load.php');
    process.exit(1);
  }
}

/* Determine paths based on theme type */
var themeInfo = getActiveChildTheme();
var isChild = themeInfo.is_child;
var currentThemePath = isChild ? themeInfo.child_theme_path : themeInfo.parent_theme_path;
var parentThemePath = themeInfo.parent_theme_path;

// Отладочная информация
if (process.env.DEBUG_GULP) {
  console.log('DEBUG: themeInfo =', JSON.stringify(themeInfo, null, 2));
  console.log('DEBUG: isChild =', isChild);
  console.log('DEBUG: currentThemePath =', currentThemePath);
  console.log('DEBUG: parentThemePath =', parentThemePath);
}

/* Log theme information */
console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
if (isChild) {
  console.log('✓ Активная дочерняя тема: ' + themeInfo.child_theme_name);
  console.log('  Родительская тема: codeweber');
  console.log('  dist будет создан в: ' + currentThemePath + '/dist');
  console.log('  src будет использован из: ' + parentThemePath + '/src');
} else {
  console.log('✓ Работаем в родительской теме: codeweber');
  console.log('  Дочерняя тема не активна');
  console.log('  dist будет создан в: ' + currentThemePath + '/dist');
}
console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

/* Check if src exists in current theme, otherwise use parent */
function getSrcPath(relativePath) {
  var currentSrc = path_module.join(currentThemePath, relativePath);
  var parentSrc = path_module.join(parentThemePath, relativePath);
  
  // If we're in child theme and src doesn't exist here, use parent
  if (isChild && !fs.existsSync(path_module.join(currentThemePath, 'src'))) {
    return parentSrc;
  }
  // Otherwise use current theme's src (or parent if not child)
  return currentSrc;
}

/* Calculate relative path from current theme to parent theme */
var srcBasePath = isChild 
  ? path_module.relative(currentThemePath, parentThemePath).replace(/\\/g, '/') 
  : '.';
if (srcBasePath === '') {
  srcBasePath = '.';
}
var srcPrefix = srcBasePath === '.' ? 'src' : srcBasePath + '/src';

// Убеждаемся, что директория dist существует в дочерней теме
if (isChild) {
  var distDir = path_module.join(currentThemePath, 'dist');
  if (!fs.existsSync(distDir)) {
    fs.mkdirSync(distDir, { recursive: true });
    console.log('✓ Создана директория dist в дочерней теме: ' + distDir);
  }
}

/* Paths */
// Вычисляем абсолютные пути для dist в текущей теме (дочерней, если активна)
var distBasePath = currentThemePath;
var path = {
  dev: {
    html: "dev/",
    js: "dev/assets/js/",
    css: "dev/assets/css/",
    style: "dev/assets/css/",
    fontcss: "dev/assets/css/fonts/",
    colorcss: "dev/assets/css/colors/",
    img: "dev/assets/img/",
    fonts: "dev/assets/fonts/",
    media: "dev/assets/media/",
    php: "dev/assets/php/",
  },
  dist: {
    html: path_module.join(distBasePath, "dist"),
    js: path_module.join(distBasePath, "dist/assets/js"),
    css: path_module.join(distBasePath, "dist/assets/css"),
    style: path_module.join(distBasePath, "dist/assets/css"),
    fontcss: path_module.join(distBasePath, "dist/assets/css/fonts"),
    colorcss: path_module.join(distBasePath, "dist/assets/css/colors"),
    img: path_module.join(distBasePath, "dist/assets/img"),
    fonts: path_module.join(distBasePath, "dist/assets/fonts"),
    media: path_module.join(distBasePath, "dist/assets/media"),
    php: path_module.join(distBasePath, "dist/assets/php"),
  },
  src: {
    // Всегда используем src из родительской темы
    base: srcPrefix,
    html: [
      srcPrefix + "/**/*.html",
      "!" + srcPrefix + "/partials/**/*.html",
      "!" + srcPrefix + "/assets/php/**/*.html"
    ],
    partials: srcPrefix + "/partials/",
    js: srcPrefix + "/assets/js/",
    vendorjs: srcPrefix + "/assets/js/vendor/*.*",
    themejs: srcPrefix + "/assets/js/theme.js",
    restapijs: srcPrefix + "/assets/js/restapi.js",
    testimonialformjs: srcPrefix + "/assets/js/testimonial-form.js",
    ajaxdownloadjs: srcPrefix + "/assets/js/ajax-download.js",
    ajaxfilterjs: srcPrefix + "/assets/js/ajax-filter.js",
    sharebuttonsjs: srcPrefix + "/assets/js/share-buttons.js",
    formvalidationjs: srcPrefix + "/assets/js/form-validation.js",
    cf7acceptancerequiredjs: srcPrefix + "/assets/js/cf7-acceptance-required.js",
    cf7successmessagejs: srcPrefix + "/assets/js/cf7-success-message.js",
    cf7utmtrackingjs: srcPrefix + "/assets/js/cf7-utm-tracking.js",
    style: srcPrefix + "/assets/scss/style.scss",
    fontcss: srcPrefix + "/assets/scss/fonts/*.*",
    colorcss: [
      srcPrefix + "/assets/scss/colors/*.scss",
      srcPrefix + "/assets/scss/theme/_colors.scss",
    ],
    vendorcss: srcPrefix + "/assets/css/vendor/*.*",
    img: srcPrefix + "/assets/img/**/*.*",
    fonts: srcPrefix + "/assets/fonts/**/*.*",
    media: srcPrefix + "/assets/media/**/*.*",
    php: srcPrefix + "/assets/php/**/*.*",
  },
  watch: {
    // Всегда следим за src в родительской теме
    html: [
      srcPrefix + "/**/*.html", 
      "!" + srcPrefix + "/assets/php/**/*.html"
    ],
    partials: srcPrefix + "/partials/**/*.*",
    themejs: srcPrefix + "/assets/js/theme.js",
    vendorjs: srcPrefix + "/assets/js/vendor/*.*",
    css: [
      srcPrefix + "/assets/scss/**/*.scss",
      "!" + srcPrefix + "/assets/scss/fonts/*.scss",
      "!" + srcPrefix + "/assets/scss/colors/*.scss",
      "!" + srcPrefix + "/assets/scss/theme/_colors.scss",
    ],
    fontcss: srcPrefix + "/assets/scss/fonts/*.scss",
    colorcss: [
      srcPrefix + "/assets/scss/colors/*.scss",
      srcPrefix + "/assets/scss/theme/_colors.scss",
    ],
    vendorcss: srcPrefix + "/assets/css/vendor/*.*",
    img: srcPrefix + "/assets/img/**/*.*",
    fonts: srcPrefix + "/assets/fonts/**/*.*",
    media: srcPrefix + "/assets/media/**/*.*",
    php: srcPrefix + "/assets/php/",
    user: srcPrefix + "/assets/scss/_user-variables.scss",
  },
  clean: {
    dev: "dev/*",
    dist: path_module.join(distBasePath, "dist/*"),
  },
};

/* Include gulp and plugins */
var gulp = require('gulp'),
    webserver = require('browser-sync'),
    reload = webserver.reload,
    plumber = require('gulp-plumber'),
    sourcemaps = require('gulp-sourcemaps'),
    sass = require('gulp-sass')(require('sass')),
    sassUnicode = require('gulp-sass-unicode'),
    autoprefixer = require('gulp-autoprefixer'),
    cleanCSS = require('gulp-clean-css'),
    uglify = require('gulp-uglify'),
    cache = require('gulp-cache'),
    imagemin = require('gulp-imagemin'),
    jpegrecompress = require('imagemin-jpeg-recompress'),
    pngquant = require('imagemin-pngquant'),
    del = require('del'),
    fileinclude = require('gulp-file-include'),
    beautify = require('gulp-beautify'),
    minify = require('gulp-minify'),
    concat = require('gulp-concat'),
    jsImport = require('gulp-js-import'),
    newer = require('gulp-newer'),
    replace = require('gulp-replace'),
    touch = require('gulp-touch-cmd');
    
/* Server */
var config = {
    server: {
        baseDir: './dist'
    },
    ghostMode: false, // By setting true, clicks, scrolls and form inputs on any device will be mirrored to all others
    notify: false
};

/* Tasks */

// Start the server
gulp.task('webserver', function () {
    webserver(config);
});

gulp.task("restapijs:dev", function () {
  return gulp
    .src(path.src.restapijs)
    .pipe(gulp.dest(path.dev.js))
    .pipe(plumber())
    .pipe(gulp.dest(path.dev.js))
    .pipe(touch());
});

gulp.task("restapijs:dist", function () {
  return (
    gulp
      .src(path.src.restapijs)
      .pipe(gulp.dest(path.dist.js))
      .pipe(plumber())
      //.pipe(uglify()) // если нужно минифицировать — раскомментируй
      .pipe(gulp.dest(path.dist.js))
      .on("end", () => {
        reload();
      })
  );
});

gulp.task("testimonialformjs:dev", function () {
  return gulp
    .src(path.src.testimonialformjs)
    .pipe(gulp.dest(path.dev.js))
    .pipe(plumber())
    .pipe(gulp.dest(path.dev.js))
    .pipe(touch());
});

gulp.task("testimonialformjs:dist", function () {
  return (
    gulp
      .src(path.src.testimonialformjs)
      .pipe(gulp.dest(path.dist.js))
      .pipe(plumber())
      //.pipe(uglify()) // если нужно минифицировать — раскомментируй
      .pipe(gulp.dest(path.dist.js))
      .on("end", () => {
        reload();
      })
  );
});

gulp.task("ajaxdownloadjs:dev", function () {
  return gulp
    .src(path.src.ajaxdownloadjs)
    .pipe(gulp.dest(path.dev.js))
    .pipe(plumber())
    .pipe(gulp.dest(path.dev.js))
    .pipe(touch());
});

gulp.task("ajaxdownloadjs:dist", function () {
  return (
    gulp
      .src(path.src.ajaxdownloadjs)
      .pipe(gulp.dest(path.dist.js))
      .pipe(plumber())
      //.pipe(uglify()) // если нужно минифицировать — раскомментируй
      .pipe(gulp.dest(path.dist.js))
      .on("end", () => {
        reload();
      })
  );
});

gulp.task("ajaxfilterjs:dev", function () {
  return gulp
    .src(path.src.ajaxfilterjs)
    .pipe(gulp.dest(path.dev.js))
    .pipe(plumber())
    .pipe(gulp.dest(path.dev.js))
    .pipe(touch());
});

gulp.task("ajaxfilterjs:dist", function () {
  return (
    gulp
      .src(path.src.ajaxfilterjs)
      .pipe(gulp.dest(path.dist.js))
      .pipe(plumber())
      //.pipe(uglify()) // если нужно минифицировать — раскомментируй
      .pipe(gulp.dest(path.dist.js))
      .on("end", () => {
        reload();
      })
  );
});

gulp.task("sharebuttonsjs:dev", function () {
  return gulp
    .src(path.src.sharebuttonsjs)
    .pipe(gulp.dest(path.dev.js))
    .pipe(plumber())
    .pipe(gulp.dest(path.dev.js))
    .pipe(touch());
});

gulp.task("sharebuttonsjs:dist", function () {
  return (
    gulp
      .src(path.src.sharebuttonsjs)
      .pipe(gulp.dest(path.dist.js))
      .pipe(plumber())
      //.pipe(uglify()) // если нужно минифицировать — раскомментируй
      .pipe(gulp.dest(path.dist.js))
      .on("end", () => {
        reload();
      })
  );
});

gulp.task("formvalidationjs:dev", function () {
  return gulp
    .src(path.src.formvalidationjs)
    .pipe(gulp.dest(path.dev.js))
    .pipe(plumber())
    .pipe(gulp.dest(path.dev.js))
    .pipe(touch());
});

gulp.task("formvalidationjs:dist", function () {
  return (
    gulp
      .src(path.src.formvalidationjs)
      .pipe(gulp.dest(path.dist.js))
      .pipe(plumber())
      //.pipe(uglify()) // если нужно минифицировать — раскомментируй
      .pipe(gulp.dest(path.dist.js))
      .on("end", () => {
        reload();
      })
  );
});

gulp.task("cf7acceptancerequiredjs:dev", function () {
  return gulp
    .src(path.src.cf7acceptancerequiredjs)
    .pipe(gulp.dest(path.dev.js))
    .pipe(plumber())
    .pipe(gulp.dest(path.dev.js))
    .pipe(touch());
});

gulp.task("cf7acceptancerequiredjs:dist", function () {
  return (
    gulp
      .src(path.src.cf7acceptancerequiredjs)
      .pipe(gulp.dest(path.dist.js))
      .pipe(plumber())
      //.pipe(uglify()) // если нужно минифицировать — раскомментируй
      .pipe(gulp.dest(path.dist.js))
      .on("end", () => {
        reload();
      })
  );
});

gulp.task("cf7successmessagejs:dev", function () {
  return gulp
    .src(path.src.cf7successmessagejs)
    .pipe(gulp.dest(path.dev.js))
    .pipe(plumber())
    .pipe(gulp.dest(path.dev.js))
    .pipe(touch());
});

gulp.task("cf7successmessagejs:dist", function () {
  return (
    gulp
      .src(path.src.cf7successmessagejs)
      .pipe(gulp.dest(path.dist.js))
      .pipe(plumber())
      //.pipe(uglify()) // если нужно минифицировать — раскомментируй
      .pipe(gulp.dest(path.dist.js))
      .on("end", () => {
        reload();
      })
  );
});

gulp.task("cf7utmtrackingjs:dev", function () {
  return gulp
    .src(path.src.cf7utmtrackingjs)
    .pipe(gulp.dest(path.dest.js));
});

gulp.task("cf7utmtrackingjs:dist", function () {
  return (
    gulp
      .src(path.src.cf7utmtrackingjs)
      .pipe(gulp.dest(path.dist.js))
      .pipe(plumber())
      //.pipe(uglify()) // если нужно минифицировать — раскомментируй
      .pipe(gulp.dest(path.dist.js))
      .on("end", () => {
        reload();
      })
  );
});

// Compile html
gulp.task('html:dev', function () {
  return gulp.src(path.src.html)
    .pipe(newer({ dest: path.dev.html, extra: path.watch.partials }))
    .pipe(plumber())
    .pipe(fileinclude({ prefix: '@@', basepath: path.src.partials }))
    .pipe(beautify.html({ indent_size: 2, preserve_newlines: false }))
    .pipe(gulp.dest(path.dev.html))
    .pipe(touch())
});
gulp.task('html:dist', function () {
  return gulp.src(path.src.html)
    .pipe(newer({ dest: path.dist.html, extra: path.watch.partials }))
    .pipe(plumber())
    .pipe(fileinclude({ prefix: '@@', basepath: path.src.partials }))
    .pipe(beautify.html({ indent_size: 2, preserve_newlines: false }))
    .pipe(gulp.dest(path.dist.html))
    .pipe(touch())
    .on('end', () => { reload(); });
});

// Compile theme styles
gulp.task('css:dev', function () {
  return gulp.src(path.src.style)
    .pipe(newer(path.dev.style))
    .pipe(plumber())
    .pipe(sass()
      .on('error', function (err) {
        sass.logError(err);
        this.emit('end');
      })
    )
    .pipe(sassUnicode())
    .pipe(autoprefixer())
    .pipe(beautify.css({ indent_size: 2, preserve_newlines: false, newline_between_rules: false }))
    .pipe(gulp.dest(path.dev.style))
    .pipe(touch())
});
gulp.task('css:dist', function () {
  return gulp.src(path.src.style)
    .pipe(newer(path.dist.style))
    .pipe(plumber())
    .pipe(sourcemaps.init())
    .pipe(sass()
      .on('error', function (err) {
        sass.logError(err);
        this.emit('end');
      })
    )
    .pipe(sassUnicode())
    .pipe(autoprefixer())
    .pipe(cleanCSS())
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest(path.dist.style))
    .pipe(touch())
    .on('end', () => { reload(); });
});

// Move fonts
gulp.task('fonts:dev', function () {
  return gulp.src(path.src.fonts)
    .pipe(newer(path.dev.fonts))
    .pipe(gulp.dest(path.dev.fonts));
});
gulp.task('fonts:dist', function () {
  return gulp.src(path.src.fonts)
    .pipe(newer(path.dist.fonts))
    .pipe(gulp.dest(path.dist.fonts));
});

// Compile font styles
gulp.task('fontcss:dev', function () {
  return gulp.src(path.src.fontcss)
    .pipe(newer(path.dev.fontcss))
    .pipe(plumber())
    .pipe(sass()
      .on('error', function (err) {
        sass.logError(err);
        this.emit('end');
      })
    )
    .pipe(sassUnicode())
    .pipe(autoprefixer())
    .pipe(cleanCSS())
    .pipe(beautify.css({ indent_size: 2, preserve_newlines: false, newline_between_rules: false }))
    .pipe(gulp.dest(path.dev.fontcss))
    .pipe(touch())
});
gulp.task('fontcss:dist', function () {
  return gulp.src(path.src.fontcss)
    .pipe(newer(path.dist.fontcss))
    .pipe(plumber())
    .pipe(sass()
      .on('error', function (err) {
        sass.logError(err);
        this.emit('end');
      })
    )
    .pipe(sassUnicode())
    .pipe(autoprefixer())
    .pipe(cleanCSS())
    .pipe(beautify.css({ indent_size: 2, preserve_newlines: false, newline_between_rules: false }))
    .pipe(gulp.dest(path.dist.fontcss))
    .pipe(touch())
    .on('end', () => { reload(); });
});

// Compile color styles
gulp.task('colorcss:dev', function () {
  return gulp.src(path.src.colorcss)
    .pipe(plumber())
    .pipe(sass()
      .on('error', function (err) {
        sass.logError(err);
        this.emit('end');
      })
    )
    .pipe(sassUnicode())
    .pipe(autoprefixer())
    .pipe(beautify.css({ indent_size: 2, preserve_newlines: false, newline_between_rules: false }))
    .pipe(gulp.dest(path.dev.colorcss))
    .pipe(touch())
});
gulp.task('colorcss:dist', function () {
  return gulp.src(path.src.colorcss)
    .pipe(plumber())
    .pipe(sass()
      .on('error', function (err) {
        sass.logError(err);
        this.emit('end');
      })
    )
    .pipe(sassUnicode())
    .pipe(autoprefixer())
    .pipe(cleanCSS())
    .pipe(gulp.dest(path.dist.colorcss))
    .pipe(touch())
    .on('end', () => { reload(); });
});

// Compile vendor styles
gulp.task('vendorcss:dev', function () {
  return gulp.src(path.src.vendorcss)
    .pipe(concat('plugins.css'))
    .pipe(beautify.css({ indent_size: 2, preserve_newlines: false, newline_between_rules: false }))
    .pipe(gulp.dest(path.dev.css))
    .pipe(touch())
});
gulp.task('vendorcss:dist', function () {
  return gulp.src(path.src.vendorcss)
    .pipe(concat('plugins.css'))
    .pipe(cleanCSS())
    .pipe(gulp.dest(path.dist.css))
    .pipe(touch())
    .on('end', () => { reload(); });
});

// Compile vendor plugins js
gulp.task('pluginsjs:dev', function() {
    return gulp.src([
      'node_modules/bootstrap/dist/js/bootstrap.bundle.js',
      path.src.vendorjs
    ])
    .pipe(jsImport({hideConsole: true}))
    .pipe(concat('plugins.js'))
    .pipe(gulp.dest(path.dev.js))
    .pipe(touch())
});
gulp.task('pluginsjs:dist', function() {
    return gulp.src([
      'node_modules/bootstrap/dist/js/bootstrap.bundle.js',
      path.src.vendorjs
    ])
    .pipe(jsImport({hideConsole: true}))
    .pipe(concat('plugins.js'))
    .pipe(uglify())
    .pipe(gulp.dest(path.dist.js))
    .pipe(touch())
    .on('end', () => { reload(); });
});

// Compile theme js
gulp.task('themejs:dev', function () {
  return gulp.src(path.src.themejs)
    .pipe(gulp.dest(path.dev.js))
    .pipe(plumber())
    .pipe(gulp.dest(path.dev.js))
});
gulp.task('themejs:dist', function () {
  return gulp.src(path.src.themejs)
    .pipe(gulp.dest(path.dist.js))
    .pipe(plumber())
    //.pipe(uglify())
    .pipe(gulp.dest(path.dist.js))
    .on('end', () => { reload(); });
});

// Move media
gulp.task('media:dev', function () {
  return gulp.src(path.src.media)
    .pipe(newer(path.dev.media))
    .pipe(gulp.dest(path.dev.media));
});
gulp.task('media:dist', function () {
  return gulp.src(path.src.media)
    .pipe(newer(path.dist.media))
    .pipe(gulp.dest(path.dist.media));
});

// Move php
gulp.task('php:dev', function () {
  return gulp.src(path.src.php)
    .pipe(newer(path.dev.php))
    .pipe(gulp.dest(path.dev.php));
});
gulp.task('php:dist', function () {
  return gulp.src(path.src.php)
    .pipe(newer(path.dist.php))
    .pipe(gulp.dest(path.dist.php));
});

// Image processing
gulp.task('image:dev', function () {
  return gulp.src(path.src.img)
    .pipe(newer(path.dev.img))
    .pipe(cache(imagemin([
      imagemin.gifsicle({ interlaced: true }),
      jpegrecompress({
        progressive: true,
        max: 90,
        min: 80
      }),
      pngquant(),
      imagemin.svgo({ plugins: [{ removeViewBox: false }] })])))
    .pipe(gulp.dest(path.dev.img));
});
gulp.task('image:dist', function () {
  return gulp.src(path.src.img)
    .pipe(newer(path.dist.img))
    .pipe(cache(imagemin([
      imagemin.gifsicle({ interlaced: true }),
      jpegrecompress({
        progressive: true,
        max: 90,
        min: 80
      }),
      pngquant(),
      imagemin.svgo({ plugins: [{ removeViewBox: false }] })
        ])))
    .pipe(gulp.dest(path.dist.img))
    .on('end', () => { reload(); });
});

// Remove catalog dev
gulp.task('clean:dev', function () {
  return del(path.clean.dev);
});
gulp.task('clean:dist', function () {
  return del(path.clean.dist);
});

// Clear cache
gulp.task('cache:clear', function () {
    cache.clearAll();
});

// Assembly Dev
gulp.task(
  "build:dev",
  gulp.series(
    "clean:dev",
    gulp.parallel(
      //"html:dev",
      "css:dev",
      "fontcss:dev",
      //"colorcss:dev",
      "vendorcss:dev",
      "pluginsjs:dev",
      "restapijs:dev",
      "testimonialformjs:dev",
      "ajaxdownloadjs:dev",
      "ajaxfilterjs:dev",
      "sharebuttonsjs:dev",
      "formvalidationjs:dev",
      "cf7acceptancerequiredjs:dev",
      "cf7successmessagejs:dev",
      "cf7utmtrackingjs:dev",
      "themejs:dev",
      "fonts:dev",
      "media:dev",
      //"php:dev",
      "image:dev"
    )
  )
);

// Assembly Dist
gulp.task(
  "build:dist",
  gulp.series(
    "clean:dist",
    gulp.parallel(
      //"html:dist",
      "css:dist",
      "fontcss:dist",
      //"colorcss:dist",
      "vendorcss:dist",
      "pluginsjs:dist",
      "restapijs:dist",
      "testimonialformjs:dist",
      "ajaxdownloadjs:dist",
      "ajaxfilterjs:dist",
      "sharebuttonsjs:dist",
      "formvalidationjs:dist",
      "cf7acceptancerequiredjs:dist",
      "cf7successmessagejs:dist",
      "cf7utmtrackingjs:dist",
      "themejs:dist",
      "fonts:dist",
      "media:dist",
      //"php:dist",
      "image:dist"
    )
  )
);


// Launching tasks when files change
gulp.task('watch', function () {
    gulp.watch(path.watch.html, gulp.series('html:dist'));
    gulp.watch(path.watch.css, gulp.series('css:dist'));
    gulp.watch(path.watch.fontcss, gulp.series('fontcss:dist'));
    gulp.watch(path.watch.colorcss, gulp.series('colorcss:dist'));
    gulp.watch(path.watch.vendorcss, gulp.series('vendorcss:dist'));
    gulp.watch(path.watch.vendorjs, gulp.series('pluginsjs:dist'));
    gulp.watch(path.watch.themejs, gulp.series('themejs:dist'));
    gulp.watch(path.src.restapijs, gulp.series('restapijs:dist'));
    gulp.watch(path.src.testimonialformjs, gulp.series('testimonialformjs:dist'));
    gulp.watch(path.src.ajaxdownloadjs, gulp.series('ajaxdownloadjs:dist'));
    gulp.watch(path.src.ajaxfilterjs, gulp.series('ajaxfilterjs:dist'));
    gulp.watch(path.src.sharebuttonsjs, gulp.series('sharebuttonsjs:dist'));
    gulp.watch(path.src.formvalidationjs, gulp.series('formvalidationjs:dist'));
    gulp.watch(path.src.cf7acceptancerequiredjs, gulp.series('cf7acceptancerequiredjs:dist'));
    gulp.watch(path.src.cf7successmessagejs, gulp.series('cf7successmessagejs:dist'));
    gulp.watch(path.src.cf7utmtrackingjs, gulp.series('cf7utmtrackingjs:dist'));
    gulp.watch(path.watch.img, gulp.series('image:dist'));
    gulp.watch(path.watch.fonts, gulp.series('fonts:dist'));
    gulp.watch(path.watch.media, gulp.series('media:dist'));
    gulp.watch(path.watch.php, gulp.series('php:dist'));
    gulp.watch(path.watch.user, gulp.series('colorcss:dist'));
});

// Serve
gulp.task('serve', gulp.series(
    gulp.parallel('webserver','watch')
));

// Dev
gulp.task('build:dev', gulp.series(
    'build:dev'
));

// Dist
gulp.task('build:dist', gulp.series(
    'build:dist'
));

// Default tasks
gulp.task('default', gulp.series(
    'build:dist',
    gulp.parallel('webserver','watch')
));
