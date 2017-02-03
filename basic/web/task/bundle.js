'use strict'

let gulp = require("gulp"),
    del = require('del'),
    path = require("path"),
    inject = require("gulp-inject"),
    injectString = require("gulp-inject-string"),
    uglify = require('gulp-uglify'),
    concat = require('gulp-concat'),
    rename = require("gulp-rename"),
    postcss = require("gulp-postcss"),
    autoprefixer = require("autoprefixer"),
    cssnano = require("cssnano");

const sourceMap = [].concat(
    require("./sourcemap/common.json"),
    require("./sourcemap/main.json"),
    require("./sourcemap/project.json"),
    require("./sourcemap/task.json"),
    require("./sourcemap/attendance.json"),
    require("./sourcemap/notice.json"),
    require("./sourcemap/meeting.json"),
    require("./sourcemap/survey.json"),
    require("./sourcemap/workstatement.json"),
    require("./sourcemap/system.json"),
    require("./sourcemap/apply.json"),
    require("./sourcemap/applymobile.json"),
    require("./sourcemap/colleague.json"),
    require("./sourcemap/personal.json"),
    require("./sourcemap/msg.json"),
    require("./sourcemap/cloud.json")
)


const vendorMap = require("./sourcemap/vendor.json")
const cssMap = require("./sourcemap/css.json")

//生成资源打包时间戳后缀
const assetSalt = Date.now()

//入口模板文件
const entryHTML = "./index-inject.html"

//第三方js包
gulp.task("vendor.js", () => {
    return gulp.src(vendorMap)
        .pipe(uglify({
            mangle: false
        }))
        .pipe(concat("vendor.min.js"))
        .pipe(rename(path => {
            path.basename += `-${assetSalt}`
            return path
        }))
        .pipe(gulp.dest("./dist"))
})

//业务js包
gulp.task("bundle.js", () => {
    return gulp.src(sourceMap)
        .pipe(uglify({
            mangle: false
        }))
        .pipe(concat("oa.min.js"))
        .pipe(rename(path => {
            path.basename += `-${assetSalt}`
            return path
        }))
        .pipe(gulp.dest("./dist"))
})

//css打包
gulp.task("bundle.css", () => {
    const processors = [
        autoprefixer({ browsers: ['last 1 version'] }),
        cssnano()
    ]
    return gulp.src(cssMap).pipe(postcss(processors))
        .pipe(concat("oa.min.css"))
        .pipe(rename(path => {
            path.basename += `-${assetSalt}`
            return path
        }))
        .pipe(gulp.dest("./dist"))
})

//清理打包目录
gulp.task("clean",()=>{
    del.sync("./dist/**")
})

//注入模板文件
function bundledTime(){
    const dateObj = new Date()
    const year = dateObj.getFullYear()
    const month = dateObj.getMonth() + 1
    const date = dateObj.getDate()
    const hour = dateObj.getHours()
    const minute = dateObj.getMinutes()
    return ""+year+month+date+hour+minute
}
gulp.task("inject2html",()=>{
    const sources = gulp.src(['./dist/oa*.css','./dist/vendor*.js','./dist/oa*.js'])
    const injectPath = path.dirname(entryHTML)
    gulp.src(entryHTML).pipe(inject(sources,{
        relative:true
    }))
    .pipe(injectString.replace('<meta name="bundledAt" content="\d{12}">',''))
    .pipe(injectString.before("<link",'<meta name="bundledAt" content="'+bundledTime()+'">\n'))
    .pipe(gulp.dest(injectPath))
})

gulp.task("bundle", ["clean", "bundle.js"])