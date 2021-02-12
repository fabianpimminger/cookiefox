import CookieFox from './CookieFox.svelte';

const replaceContainer = function ( Component, options ) {
    const frag = document.createDocumentFragment();
    const component = new Component( Object.assign( {}, options, { target: frag } ));

    options.target.replaceWith( frag );

    return component;
}

const app = replaceContainer( CookieFox, {
  target: document.getElementById("cookiefox"),
	props: {
		data: window.cookiefox
	}
});

export default app;
