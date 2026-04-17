/**
 * PrestaChamps
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Commercial License
 * you can't distribute, modify or sell this code
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file
 * If you need help please contact leo@prestachamps.com
 *
 * @author    Mailchimp
 * @copyright Mailchimp
 * @license   commercial
 */
axios.interceptors.request.use((config) => {
    if (!config.headers.post.hasOwnProperty('SkipLoadingScreen')) {
        window.app.beforeHttpRequest();
    }
    return config;
});

axios.interceptors.response.use((config) => {
    window.app.afterHttpRequest();
    return config;
});