export function resolveResponsiveStyles(styles = {}, viewport = 'desktop') {
    const desktop = styles?.desktop || {};
    const tablet = styles?.tablet || {};
    const mobile = styles?.mobile || {};

    if (viewport === 'mobile') {
        return { ...desktop, ...tablet, ...mobile };
    }
    if (viewport === 'tablet') {
        return { ...desktop, ...tablet };
    }
    return { ...desktop };
}

export function isNodeHidden(advanced = {}, viewport = 'desktop') {
    if (!advanced) return false;
    if (viewport === 'desktop' && advanced.hideOnDesktop) return true;
    if (viewport === 'tablet' && advanced.hideOnTablet) return true;
    if (viewport === 'mobile' && advanced.hideOnMobile) return true;
    return false;
}
