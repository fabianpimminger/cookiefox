var app=function(){"use strict";function t(){}function e(t){return t()}function n(){return Object.create(null)}function o(t){t.forEach(e)}function i(t){return"function"==typeof t}function r(t,e){return t!=t?e==e:t!==e||t&&"object"==typeof t||"function"==typeof t}function c(t,e){t.appendChild(e)}function a(t,e,n){t.insertBefore(e,n||null)}function u(t){t.parentNode.removeChild(t)}function s(t){return document.createElement(t)}function f(t){return document.createTextNode(t)}function l(){return f(" ")}function d(t,e,n,o){return t.addEventListener(e,n,o),()=>t.removeEventListener(e,n,o)}function p(t,e,n){null==n?t.removeAttribute(e):t.getAttribute(e)!==n&&t.setAttribute(e,n)}function _(t,e){e=""+e,t.wholeText!==e&&(t.data=e)}let m;function g(t){m=t}function h(t){(function(){if(!m)throw new Error("Function called outside component initialization");return m})().$$.on_mount.push(t)}const x=[],b=[],v=[],k=[],y=Promise.resolve();let $=!1;function w(t){v.push(t)}let E=!1;const C=new Set;function A(){if(!E){E=!0;do{for(let t=0;t<x.length;t+=1){const e=x[t];g(e),N(e.$$)}for(g(null),x.length=0;b.length;)b.pop()();for(let t=0;t<v.length;t+=1){const e=v[t];C.has(e)||(C.add(e),e())}v.length=0}while(x.length);for(;k.length;)k.pop()();$=!1,E=!1,C.clear()}}function N(t){if(null!==t.fragment){t.update(),o(t.before_update);const e=t.dirty;t.dirty=[-1],t.fragment&&t.fragment.p(t.ctx,e),t.after_update.forEach(w)}}const S=new Set;function I(t,e){-1===t.$$.dirty[0]&&(x.push(t),$||($=!0,y.then(A)),t.$$.dirty.fill(0)),t.$$.dirty[e/31|0]|=1<<e%31}function O(r,c,a,s,f,l,d=[-1]){const p=m;g(r);const _=c.props||{},h=r.$$={fragment:null,ctx:null,props:l,update:t,not_equal:f,bound:n(),on_mount:[],on_destroy:[],before_update:[],after_update:[],context:new Map(p?p.$$.context:[]),callbacks:n(),dirty:d,skip_bound:!1};let x=!1;if(h.ctx=a?a(r,_,((t,e,...n)=>{const o=n.length?n[0]:e;return h.ctx&&f(h.ctx[t],h.ctx[t]=o)&&(!h.skip_bound&&h.bound[t]&&h.bound[t](o),x&&I(r,t)),e})):[],h.update(),x=!0,o(h.before_update),h.fragment=!!s&&s(h.ctx),c.target){if(c.hydrate){const t=function(t){return Array.from(t.childNodes)}(c.target);h.fragment&&h.fragment.l(t),t.forEach(u)}else h.fragment&&h.fragment.c();c.intro&&((b=r.$$.fragment)&&b.i&&(S.delete(b),b.i(v))),function(t,n,r){const{fragment:c,on_mount:a,on_destroy:u,after_update:s}=t.$$;c&&c.m(n,r),w((()=>{const n=a.map(e).filter(i);u?u.push(...n):o(n),t.$$.on_mount=[]})),s.forEach(w)}(r,c.target,c.anchor),A()}var b,v;g(p)}var T,B,D=(function(t,e){var n;n=function(){function t(){for(var t=0,e={};t<arguments.length;t++){var n=arguments[t];for(var o in n)e[o]=n[o]}return e}function e(t){return t.replace(/(%[0-9A-Z]{2})+/g,decodeURIComponent)}return function n(o){function i(){}function r(e,n,r){if("undefined"!=typeof document){"number"==typeof(r=t({path:"/"},i.defaults,r)).expires&&(r.expires=new Date(1*new Date+864e5*r.expires)),r.expires=r.expires?r.expires.toUTCString():"";try{var c=JSON.stringify(n);/^[\{\[]/.test(c)&&(n=c)}catch(s){}n=o.write?o.write(n,e):encodeURIComponent(String(n)).replace(/%(23|24|26|2B|3A|3C|3E|3D|2F|3F|40|5B|5D|5E|60|7B|7D|7C)/g,decodeURIComponent),e=encodeURIComponent(String(e)).replace(/%(23|24|26|2B|5E|60|7C)/g,decodeURIComponent).replace(/[\(\)]/g,escape);var a="";for(var u in r)r[u]&&(a+="; "+u,!0!==r[u]&&(a+="="+r[u].split(";")[0]));return document.cookie=e+"="+n+a}}function c(t,n){if("undefined"!=typeof document){for(var i={},r=document.cookie?document.cookie.split("; "):[],c=0;c<r.length;c++){var a=r[c].split("="),u=a.slice(1).join("=");n||'"'!==u.charAt(0)||(u=u.slice(1,-1));try{var s=e(a[0]);if(u=(o.read||o)(u,s)||e(u),n)try{u=JSON.parse(u)}catch(f){}if(i[s]=u,t===s)break}catch(f){}}return t?i[t]:i}}return i.set=r,i.get=function(t){return c(t,!1)},i.getJSON=function(t){return c(t,!0)},i.remove=function(e,n){r(e,"",t(n,{expires:-1}))},i.defaults={},i.withConverter=n,i}((function(){}))},t.exports=n()}(B={path:T,exports:{},require:function(t,e){return function(){throw new Error("Dynamic requires are not currently supported by @rollup/plugin-commonjs")}(null==e&&B.path)}},B.exports),B.exports);function j(t){let e,n,o,i,r=t[0].notice_button_decline+"";return{c(){e=s("button"),n=f(r),p(e,"class","cookiefox__button cookiefox__button--secondary"),p(e,"tabindex","1")},m(r,u){a(r,e,u),c(e,n),o||(i=d(e,"click",t[3]),o=!0)},p(t,e){1&e&&r!==(r=t[0].notice_button_decline+"")&&_(n,r)},d(t){t&&u(e),o=!1,i()}}}function L(e){let n,o,i,r,m,g,h,x,b,v,k,y,$,w,E,C,A,N=e[0].notice_title+"",S=e[0].notice_text+"",I=e[0].notice_button_accept+"",O="on"===e[0].notice_button_decline_enabled&&j(e);return{c(){n=s("div"),o=s("div"),i=s("div"),r=s("h3"),m=f(N),g=l(),h=s("div"),x=l(),b=s("footer"),O&&O.c(),v=l(),k=s("button"),y=f(I),p(r,"class","cookiefox__title"),p(h,"class","cookiefox__text"),p(i,"class","cookiefox__body"),p(k,"class","cookiefox__button cookiefox__button--primary"),p(k,"tabindex","1"),p(b,"class","cookiefox__footer"),p(o,"class","cookiefox__inner"),p(n,"class",$="cookiefox cookiefox--"+e[0].notice_display),p(n,"style",w=void 0===e[1]?"display: flex;":""),p(n,"aria-hidden",E=void 0===e[1]?"true":"false")},m(t,u){a(t,n,u),c(n,o),c(o,i),c(i,r),c(r,m),c(i,g),c(i,h),h.innerHTML=S,c(o,x),c(o,b),O&&O.m(b,null),c(b,v),c(b,k),c(k,y),C||(A=d(k,"click",e[2]),C=!0)},p(t,[e]){1&e&&N!==(N=t[0].notice_title+"")&&_(m,N),1&e&&S!==(S=t[0].notice_text+"")&&(h.innerHTML=S),"on"===t[0].notice_button_decline_enabled?O?O.p(t,e):(O=j(t),O.c(),O.m(b,v)):O&&(O.d(1),O=null),1&e&&I!==(I=t[0].notice_button_accept+"")&&_(y,I),1&e&&$!==($="cookiefox cookiefox--"+t[0].notice_display)&&p(n,"class",$),2&e&&w!==(w=void 0===t[1]?"display: flex;":"")&&p(n,"style",w),2&e&&E!==(E=void 0===t[1]?"true":"false")&&p(n,"aria-hidden",E)},i:t,o:t,d(t){t&&u(n),O&&O.d(),C=!1,A()}}}function U(t,e,n){let o,{data:i}=e;function r(){var t;if(!0===o.consent?void 0!==i.scripts_consent&&""!==i.scripts_consent&&(t=i.scripts_consent):void 0!==i.scripts_no_consent&&""!==i.scripts_no_consent&&(t=i.scripts_no_consent),void 0!==t&&""!==t){let n=document.createElement("div");n.innerHTML=t,document.body.appendChild(n);let o=n.getElementsByTagName("script"),i=-1;for(;++i<o.length;){var e=document.createElement("script");e.text=o[i].innerHTML;let t,n=-1,r=o[i].attributes;for(;++n<r.length;)e.setAttribute((t=r[n]).name,t.value);o[i].parentNode.replaceChild(e,o[i])}}}function c(){void 0!==i.cookie_name&&""!==i.cookie_name&&i.cookie_name;let t={};void 0!==i.cookie_expiration&&""!==i.cookie_expiration&&(t.expires=parseInt(i.cookie_expiration)),void 0!==i.cookie_domain&&""!==i.cookie_domain&&(t.domain=parseInt(i.cookie_expiration)),D.set(i.cookie_name,o,t)}return h((()=>{if(/bot|googlebot|crawler|spider|robot|crawling/i.test(navigator.userAgent))return n(1,o={consent:!1}),void r();n(1,o=D.get(i.cookie_name)),void 0!==o&&(n(1,o=JSON.parse(o)),r(),c())})),t.$$set=t=>{"data"in t&&n(0,i=t.data)},[i,o,function(){n(1,o={consent:!0}),r(),c()},function(){n(1,o={consent:!1}),r(),c()}]}return function(t,e){const n=document.createDocumentFragment(),o=new t(Object.assign({},e,{target:n}));return e.target.replaceWith(n),o}(class extends class{$destroy(){!function(t,e){const n=t.$$;null!==n.fragment&&(o(n.on_destroy),n.fragment&&n.fragment.d(e),n.on_destroy=n.fragment=null,n.ctx=[])}(this,1),this.$destroy=t}$on(t,e){const n=this.$$.callbacks[t]||(this.$$.callbacks[t]=[]);return n.push(e),()=>{const t=n.indexOf(e);-1!==t&&n.splice(t,1)}}$set(t){var e;this.$$set&&(e=t,0!==Object.keys(e).length)&&(this.$$.skip_bound=!0,this.$$set(t),this.$$.skip_bound=!1)}}{constructor(t){super(),O(this,t,U,L,r,{data:0})}},{target:document.getElementById("cookiefox"),props:{data:window.cookiefox}})}();
