import * as esbuild from 'esbuild'

const isDev = process.argv.includes('--dev')

const builds = [
    {
        entryPoints: ['./resources/js/index.js'],
        outfile: './dist/undo-toast.js',
        bundle: true,
        format: 'esm',
        platform: 'neutral',
        target: ['es2020'],
    },
    {
        entryPoints: ['./resources/css/index.css'],
        outfile: './dist/undo-toast.css',
        bundle: true,
    },
]

for (const options of builds) {
    const context = await esbuild.context({
        ...options,
        minify: !isDev,
        sourcemap: isDev ? 'inline' : false,
        logLevel: 'info',
    })

    if (isDev) {
        await context.watch()
    } else {
        await context.rebuild()
        await context.dispose()
    }
}
