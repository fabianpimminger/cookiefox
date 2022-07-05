import { writable } from 'svelte/store';

export const forceNotice = writable(false);
export const cookie = writable(null);