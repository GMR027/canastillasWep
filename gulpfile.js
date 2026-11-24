import { src, dest, watch, series, parallel } from 'gulp'
import * as dartSass from 'sass'
import gulpSass from 'gulp-sass'

const sass = gulpSass(dartSass)

export function css() {
  return src('src/scss/sitio.scss', { sourcemaps: true })
    .pipe(sass({ outputStyle: 'compressed' }).on('error', sass.logError))
    .pipe(dest('build/css', { sourcemaps: '.' }))
}

export function js() {
  return src(['src/js/sitio.js', 'src/js/sitio-inicio.js', 'src/js/sitio-modal.js'])
    .pipe(dest('build/js'))
}

export function imagenes() {
  return src('src/img/**/*', { encoding: false }).pipe(dest('build/img'))
}

export function dev() {
  watch('src/scss/**/*.scss', css)
  watch('src/js/**/*.js', js)
  watch('src/img/**/*', imagenes)
}

export const build = parallel(css, js, imagenes)

export default series(build, dev)
