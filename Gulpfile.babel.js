
import { src, dest, watch, series, parallel } from 'gulp';
import yargs from 'yargs';
import sass from 'gulp-sass';
import cleanCss from 'gulp-clean-css';
import gulpif from 'gulp-if';
import postcss from 'gulp-postcss';
import sourcemaps from 'gulp-sourcemaps';
import autoprefixer from 'autoprefixer';
import imagemin from 'gulp-imagemin';
import del from 'del';
import webpack from 'webpack-stream';
import named from 'vinyl-named';
import browserSync from 'browser-sync';
import zip from 'gulp-zip';
import info from './package.json';
import replace from 'gulp-replace';
import wpPot from 'gulp-wp-pot';

const PRODUCTION = yargs.argv.prod;
const serverProxy = 'http://localhost/yourFolderName';
const paths = {
    styles: {
        watch: 'src/scss/**/*.scss',
        src: 'src/scss/bundle.scss',
        dest: 'dist/css',
    },
    scripts: {
        watch: 'src/js/**/*.js',
        src: ['src/js/bundle.js'],
        dest: 'dist/js',
    },
    images: {
        watch: 'src/images/**/*.{jpg,jpeg,png,svg,gif}',
        src: 'src/images/**/*.{jpg,jpeg,png,svg,gif}',
        dest: 'dist/images',
    },
    copy: {
        watch: ['src/**/*','!src/{images,js,scss}','!src/{images,js,scss}/**/*'],
        src: ['src/**/*','!src/{images,js,scss}','!src/{images,js,scss}/**/*'],
        dest: 'dist',
    },
    php: {
        watch: '**/*.php',
        src: '',
        dest: '',
    }
};

const server = browserSync.create();

export const serve = done => {
    server.init({
        proxy: serverProxy,
    });
    
    done();
};

export const reload = done => {
    server.reload();
    
    done();
};

export const styles = () => {
    return src(paths.styles.src)
        .pipe(gulpif(!PRODUCTION, sourcemaps.init()))
        .pipe(sass().on('error', sass.logError))
        .pipe(gulpif(PRODUCTION, postcss([ autoprefixer ])))
        .pipe(gulpif(PRODUCTION, cleanCss({ compatibility: 'ie8' })))
        .pipe(gulpif(!PRODUCTION, sourcemaps.write()))
        .pipe(dest(paths.styles.dest))
        .pipe(server.stream());
}

export const scripts = () => {
    return src(paths.scripts.src)
        .pipe(named())
        .pipe(webpack({
            module: {
                rules: [
                    {
                        test: /\.js$/,
                        use: {
                            loader: 'babel-loader',
                            options: {
                                presets: ['@babel/preset-env'],
                            },
                        },
                    },
                ],
            },
            mode: PRODUCTION ? 'production' : 'development',
            devtool: !PRODUCTION ? 'inline-source-map' : false,
            output: {
                filename: '[name].js'
            },
        }))
        .pipe(dest(paths.scripts.dest));
}

export const images = () => {
    return src(paths.images.src)
        .pipe(gulpif(PRODUCTION, imagemin()))
        .pipe(dest(paths.styles.dest));
}

export const copy = () => {
    return src(paths.copy.src)
        .pipe(dest(paths.copy.src));
}

export const clean = () => del(['dist']);

export const compress = () => {
    return src([
            "**/*",
            "!node_modules{,/**}",
            "!bundled{,/**}",
            "!src{,/**}",
            "!.babelrc",
            "!.gitignore",
            "!gulpfile.babel.js",
            "!package.json",
            "!package-lock.json",
        ])
        .pipe(gulpif(
            file => file.relative.split(".").pop() !== "zip",
            replace('_themename', info.name)
        ))
        .pipe(zip(`${info.name}.zip`))
        .pipe(dest('bundled'));
};

export const pot = () => {
    return src("**/*.php")
    .pipe(
        wpPot({
            domain: '_themename',
            package: info.prefix
        })
    )
    .pipe(dest(`languages/${info.prefix}.pot`));
};

export const watchChanges = () => {
    watch(paths.styles.watch, styles);
    watch(paths.images.watch, series(images, reload));
    watch(paths.copy.watch, series(copy, reload));
    watch(paths.scripts.watch, series(scripts, reload));
    watch(paths.php.watch, reload);
}

export const dev = series(clean, parallel(styles, images, copy, scripts), serve, watchChanges);
export const build = series(clean, parallel(styles, images, copy, scripts), pot, compress);
export default dev;
