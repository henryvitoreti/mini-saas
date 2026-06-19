import { useState } from '#app';

const fullLogoDark = 'images/full-logo-dark.svg';
const fullLogoLight = 'images/full-logo-light.svg';
const logoDark = 'images/logo-dark.svg';
const logoLight = 'images/logo-light.svg';
const logoIconDark = 'images/logo-icon-dark.svg';
const logoIconLight = 'images/logo-icon-light.svg';

function getLogoPath(path: string): string {
  return `/${path}`;
}

function isDarkTheme(): boolean {
  return useState<boolean>('theme-is-dark', () => false).value;
}

export function getFullLogo(): string {
  return getLogoPath(isDarkTheme() ? fullLogoDark : fullLogoLight);
}

export function getLogo(): string {
  return getLogoPath(isDarkTheme() ? logoDark : logoLight);
}

export function getLogoIcon(): string {
  return getLogoPath(isDarkTheme() ? logoIconDark : logoIconLight);
}
