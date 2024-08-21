var elixir = require('laravel-elixir');

elixir(function(mix) {
    mix.webpack('bootstrap.js', 'public/compiled/js/boostrap.js');
});
