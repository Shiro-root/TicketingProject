/**
 * Tailwind config generated from DESIGN.md.
 * Do NOT add ad-hoc colors/radii elsewhere — extend this file instead so every
 * screen in the app stays on the same token system described in DESIGN.md.
 */
import defaultTheme from 'tailwindcss/defaultTheme';

export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],
    darkMode: 'class', // toggled by the `theme` field on users (light | dark)
    theme: {
        extend: {
            colors: {
                primary: { DEFAULT: '#e60023', pressed: '#cc001f' },
                ink: '#000000',
                'ink-soft': '#211922',
                body: '#33332e',
                charcoal: '#262622',
                mute: '#62625b',
                ash: '#91918c',
                stone: '#c8c8c1',
                hairline: '#dadad3',
                'hairline-soft': '#e5e5e0',
                'on-secondary': '#000000',
                'secondary-bg': '#e5e5e0',
                'secondary-pressed': '#c8c8c1',
                canvas: '#ffffff',
                'surface-soft': '#fbfbf9',
                'surface-card': '#f6f6f3',
                'surface-elevated': '#ffffff',
                'on-dark': '#ffffff',
                'surface-dark': '#262622',
                'focus-outer': '#435ee5',
                'focus-inner': '#ffffff',
                'accent-pressed-blue': '#617bff',
                'accent-purple': '#7e238b',
                'accent-purple-deep': '#6845ab',
                'success-deep': '#103c25',
                'success-pale': '#c7f0da',
                error: '#9e0a0a',
                'error-deep': '#cc001f',
            },
            fontFamily: {
                // Pin Sans is proprietary — Inter is the documented open-source substitute.
                sans: ['Inter', 'Manrope', ...defaultTheme.fontFamily.sans],
            },
            fontSize: {
                'display-xl': ['70px', { lineHeight: '1.1', letterSpacing: '-1.2px', fontWeight: '600' }],
                'display-lg': ['44px', { lineHeight: '1.15', letterSpacing: '-0.8px', fontWeight: '700' }],
                'heading-xl': ['28px', { lineHeight: '1.2', letterSpacing: '-1.2px', fontWeight: '700' }],
                'heading-lg': ['22px', { lineHeight: '1.25', fontWeight: '600' }],
                'heading-md': ['18px', { lineHeight: '1.3', fontWeight: '600' }],
                'body-md': ['16px', { lineHeight: '1.4', fontWeight: '400' }],
                'body-strong': ['16px', { lineHeight: '1.4', fontWeight: '600' }],
                'body-sm': ['14px', { lineHeight: '1.4', fontWeight: '400' }],
                'body-sm-strong': ['14px', { lineHeight: '1.4', fontWeight: '700' }],
                'caption-md': ['12px', { lineHeight: '1.5', fontWeight: '500' }],
                'caption-sm': ['12px', { lineHeight: '1.4', fontWeight: '400' }],
                'link-md': ['16px', { lineHeight: '1.4', fontWeight: '600' }],
                'button-md': ['14px', { lineHeight: '1', fontWeight: '700' }],
                'button-sm': ['12px', { lineHeight: '1', fontWeight: '700' }],
            },
            borderRadius: {
                none: '0px',
                sm: '8px',
                md: '16px',
                lg: '32px',
                full: '9999px',
            },
            spacing: {
                xxs: '4px',
                xs: '6px',
                sm: '8px',
                md: '12px',
                lg: '16px',
                xl: '24px',
                xxl: '32px',
                section: '64px',
            },
            boxShadow: {
                modal: '0 16px 32px -8px rgba(0,0,0,0.18)',
            },
        },
    },
    plugins: [],
};
