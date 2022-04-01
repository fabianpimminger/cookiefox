import { createFocusTrap } from 'focus-trap'

var focusTrap = function(node, active) {
	let trap = createFocusTrap(node, {
    escapeDeactivates: false,
    allowOutsideClick: true,
    fallbackFocus: () => node,
	});
	
	if(active){
		trap.activate();
	}
	
	return {
		update(active) {
			if(active){
				trap.activate();
			} else {
				trap.deactivate();
			}
		},

		destroy() {
			trap.deactivate();
		},
	};
}

export default focusTrap;