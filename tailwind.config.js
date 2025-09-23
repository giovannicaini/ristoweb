module.exports = {
  content: [
    './resources/**/*.blade.php',
    './resources/views/livewire/comande/*.blade.php',
    './resources/**/*.js',
    './app/Livewire/**/*.php',
    './resources/views/filament/**/*.blade.php',
    './vendor/filament/**/*.blade.php',
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
  ],
  theme: { extend: {} },
  plugins: [require('@tailwindcss/forms'), require('@tailwindcss/typography')],
  safelist: [{ pattern: /(bg|text|border|ring)-(primary|gray|amber|green)-(50|100|200|300|500|600|700)/ }],
};