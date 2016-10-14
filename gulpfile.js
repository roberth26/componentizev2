var gulp = require( 'gulp' );
var concat = require( 'gulp-concat' );
var rename = require( 'gulp-rename' );
var insert = require( 'gulp-insert' );
var merge = require( 'gulp-merge' );
var path = require( 'path' );
var sass = require( 'gulp-sass' );
var uglify = require( 'gulp-uglify' );

gulp.task( 'functions', function() {
    return merge(
        merge(
            gulp.src( [ 'src/**/*.php', '!src/templates/**/*.php' ] ),
            gulp.src( [ 'src/templates/*.scss', 'src/components/**/*.scss' ] )
                .pipe(
                    sass({
                        outputStyle: 'compressed'
                    }).on( 'error', sass.logError )
                ).pipe(
                    rename({
                        suffix: '.min',
                        extname: '.css'
                    })
                ).pipe(
                    gulp.dest( 'dist/components' )
                ).pipe(
                    insert.transform( function( contents, file ) {
                        contents = contents.replace( new RegExp( '"', 'g' ), "'" );
                        return '<?php $mobile_css[ "' + path.basename( file.history, '.min.css' ) + '"]="' + contents + '";?>';
                    })
                )
        ),
        gulp.src( [ 'src/templates/*.js', 'src/components/**/*.js' ] )
            .pipe(
                uglify()
            ).pipe(
                rename({
                    suffix: '.min'
                })
            ).pipe(
                gulp.dest( 'dist/components' )
            ).pipe(
                insert.transform( function( contents, file ) {
                    contents = contents.replace( new RegExp( '"', 'g' ), "'" );
                    return '<?php $js[ "' + path.basename( file.history, '.min.js' ) + '"]="' + contents + '";?>';
                })
            )
    ).pipe(
        concat( 'functions.php' )
    ).pipe(
        gulp.dest( './' )
    );
});

gulp.task( 'template-php', function() {
	return gulp.src( 'src/templates/**/*.php' )
  		.pipe( rename( { dirname: '' } ) )
		.pipe( gulp.dest( './' ) );
});

gulp.task( 'template-js', function() {
    return gulp.src( 'src/templates/**/*.js' )
        .pipe( uglify() )
        .pipe( rename({
            suffix: '.min',
        })).pipe( gulp.dest( 'dist/templates' ) );
});

gulp.task( 'template-sass', function() {
    return gulp.src( 'src/templates/**/*.scss' )
        .pipe( sass( { outputStyle: 'compressed' } ).on( 'error', sass.logError ) )
        .pipe( rename({
            suffix: '.min',
            extname: '.css'
        })).pipe( gulp.dest( 'dist/templates' ) );
});

gulp.task( 'stylesheet-main', function() {
	return gulp.src( 'src/style.scss' )
        .pipe( rename({
            extname: '.css'
        })).pipe( gulp.dest( './' ) );
});

gulp.task( 'default', [
	'functions',
	'template-php',
    'template-sass',
    'template-js',
	'stylesheet-main'
]);