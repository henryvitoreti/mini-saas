import { spawn } from 'node:child_process';
import { resolve } from 'node:path';
import { subscribe } from '@parcel/watcher';

const rootDir = process.cwd();
const pagesDir = resolve(rootDir, 'app/pages');
const nuxtBin = resolve(rootDir, 'node_modules/.bin/nuxt');
const nuxtArgs = ['dev', '--host', '0.0.0.0', '--port', '3000'];

let child;
let restarting = false;
let restartTimer;
let shuttingDown = false;

const startNuxt = () => {
  child = spawn(nuxtBin, nuxtArgs, {
    cwd: rootDir,
    env: process.env,
    stdio: 'inherit',
  });

  child.on('exit', (code, signal) => {
    if (!shuttingDown && !restarting) {
      console.log(`[dev-docker] Nuxt exited (${signal ?? code}); restarting...`);
      startNuxt();
    }
  });
};

const stopNuxt = () => {
  return new Promise((resolveStop) => {
    const processToStop = child;

    if (!processToStop || processToStop.exitCode !== null) {
      resolveStop();
      return;
    }

    const killTimer = setTimeout(() => {
      if (processToStop.exitCode === null) {
        processToStop.kill('SIGKILL');
      }
    }, 5000);

    killTimer.unref();

    processToStop.once('exit', () => {
      clearTimeout(killTimer);
      resolveStop();
    });

    processToStop.kill('SIGTERM');
  });
};

const restartNuxt = async (reason) => {
  if (restarting || shuttingDown) {
    return;
  }

  restarting = true;
  console.log(`[dev-docker] Restarting Nuxt after page ${reason}...`);
  await stopNuxt();
  restarting = false;

  if (!shuttingDown) {
    startNuxt();
  }
};

const scheduleRestart = (reason) => {
  clearTimeout(restartTimer);
  restartTimer = setTimeout(() => {
    restartNuxt(reason).catch((error) => {
      console.error('[dev-docker] Failed to restart Nuxt:', error);
    });
  }, 300);
};

startNuxt();

const subscription = await subscribe(pagesDir, (error, events) => {
  if (error) {
    console.error('[dev-docker] Page watcher error:', error);
    return;
  }

  const routeStructureChanged = events.some((event) => {
    return ['create', 'delete'].includes(event.type) && event.path.endsWith('.vue');
  });

  if (routeStructureChanged) {
    scheduleRestart('structure change');
  }
});

const shutdown = async () => {
  shuttingDown = true;
  clearTimeout(restartTimer);
  await subscription.unsubscribe();
  await stopNuxt();
  process.exit(0);
};

process.on('SIGINT', shutdown);
process.on('SIGTERM', shutdown);
