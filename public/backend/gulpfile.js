const gulp = require('gulp');
const sass = require('gulp-sass');
const browserSync = require('browser-sync').create();

// compile scss into css
function style() {
    return gulp.src('./scss/style.scss')
        .pipe(sass())
        .pipe(gulp.dest('./css'))
        .pipe(browserSync.stream())
}

function watch() {
    browserSync.init({
        port: 8000,
        proxy: {
            target: "http://localhost:8000/",
        }
    });
    gulp.watch('./scss/style.scss', style);
}

exports.style = style;
exports.watch = watch;