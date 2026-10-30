import { rmSync } from 'node:fs';

/**
 * A stale public/hot file makes Laravel point @vite at a dev server that is no
 * longer running, which renders a blank page. Production builds must never
 * inherit it.
 */
rmSync('public/hot', { force: true });
