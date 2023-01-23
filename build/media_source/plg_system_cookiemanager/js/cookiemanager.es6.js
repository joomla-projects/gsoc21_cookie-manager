/**
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

((document) => {
  'use strict';

  const cookieCategories = Joomla.getOptions('plg_system_cookiemanager.categories');
  console.log({ cookieCategories });
  const cookieScripts = Joomla.getOptions('plg_system_cookiemanager.scripts');
  console.log({ cookieScripts });
  const cookieCodes = Joomla.getOptions('plg_system_cookiemanager.codes');
  console.log({ cookieCodes });
  const cookieConfig = Joomla.getOptions('plg_system_cookiemanager.config');
  console.log({ cookieConfig });

  /* global initCookieConsent */
  const cc = initCookieConsent();

  cc.run({
    current_lang: 'en',
    autoclear_cookies: true, // default: false
    page_scripts: true, // default: false
    cookie_expiration: cookieConfig.expiration, // default: 182 (days)
    cookie_name: 'jcookie', // default: 'cc_cookie'
    // mode: 'opt-in'                          // default: 'opt-in'; value: 'opt-in' or 'opt-out'
    // delay: 0,                               // default: 0
    // auto_language: null                     // default: null; could also be 'browser' or 'document'
    // autorun: true,                          // default: true
    // force_consent: false,                   // default: false
    // hide_from_bots: true,                   // default: true
    // remove_cookie_tables: false             // default: false
    // cookie_necessary_only_expiration: 182   // default: disabled
    // cookie_domain: location.hostname,       // default: current domain
    // cookie_path: '/',                       // default: root
    // cookie_same_site: 'Lax',                // default: 'Lax'
    // use_rfc_cookie: false,                  // default: false
    // revision: 0,                            // default: 0

    onFirstAction: (userPreferences, cookie) => {
      // callback triggered only once
      console.log('onFirstAction:', { userPreferences }, { cookie });
    },
    onAccept: (cookie) => {
      console.log('onAccept: ', { cookie });
      const consentDetails = {
        uuid: cookie.consent_uuid,
        url: window.location.href,
        consent_opt_in: cc.getUserPreferences().accepted_categories,
        consent_opt_out: cookie.categories,
      };
      Joomla.request({
        url: `index.php?option=com_ajax&plugin=cookiemanager&group=system&format=json&data=${JSON.stringify(consentDetails)}`,
        method: 'POST',
        onSuccess: (response) => {
          const result = JSON.parse(response);
          console.log({ result });
        },
        onError(xhr) {
          Joomla.renderMessages({ error: [xhr] }, '#system-message-container');
        },
      });
    },
    onChange: (cookie, changedPreferences) => {
      console.log('onChange: ', { cookie }, { changedPreferences });
    },
    languages: {
      en: {
        consent_modal: {
          title: Joomla.Text._('PLG_SYSTEM_COOKIEMANAGER_BANNER_TITLE'),
          description: Joomla.Text._('PLG_SYSTEM_COOKIEMANAGER_BANNER_DESC'),
          primary_btn: {
            text: Joomla.Text._('PLG_SYSTEM_COOKIEMANAGER_BANNER_BTN_ACCEPT_ALL'),
            role: 'accept_all', // 'accept_selected' or 'accept_all'
          },
          secondary_btn: {
            text: Joomla.Text._('PLG_SYSTEM_COOKIEMANAGER_BANNER_BTN_REJECT_ALL'),
            role: 'accept_necessary', // 'settings' or 'accept_necessary'
          },
        },
        settings_modal: {
          title: Joomla.Text._('PLG_SYSTEM_COOKIEMANAGER_SETTINGS_TITLE'),
          save_settings_btn: Joomla.Text._('PLG_SYSTEM_COOKIEMANAGER_SETTINGS_BTN_SAVE'),
          accept_all_btn: Joomla.Text._('PLG_SYSTEM_COOKIEMANAGER_SETTINGS_BTN_ACCEPT_ALL'),
          reject_all_btn: Joomla.Text._('PLG_SYSTEM_COOKIEMANAGER_SETTINGS_BTN_REJECT_ALL'),
          close_btn_label: Joomla.Text._('PLG_SYSTEM_COOKIEMANAGER_SETTINGS_BTN_CLOSE'),
          cookie_table_headers: [
            { col1: Joomla.Text._('PLG_SYSTEM_COOKIEMANAGER_TABLE_HEADERS_COL1') },
            { col2: Joomla.Text._('PLG_SYSTEM_COOKIEMANAGER_TABLE_HEADERS_COL2') },
            { col3: Joomla.Text._('PLG_SYSTEM_COOKIEMANAGER_TABLE_HEADERS_COL3') },
            { col4: Joomla.Text._('PLG_SYSTEM_COOKIEMANAGER_TABLE_HEADERS_COL4') },
          ],
          blocks: [
            {
              title: 'Cookie usage 📢',
              description: 'I use cookies to ensure the basic functionalities of the website and to enhance your online experience. You can choose for each category to opt-in/out whenever you want. For more details relative to cookies and other sensitive data, please read the full <a href="#" class="cc-link">privacy policy</a>.',
            }, {
              title: 'Strictly necessary cookies',
              description: 'These cookies are essential for the proper functioning of my website. Without these cookies, the website would not work properly',
              toggle: {
                value: 'necessary',
                enabled: true,
                readonly: true, // cookie categories with readonly=true are all treated as "necessary cookies"
              },
            }, {
              title: 'Performance and Analytics cookies',
              description: 'These cookies allow the website to remember the choices you have made in the past',
              toggle: {
                value: 'analytics', // your cookie category
                enabled: false,
                readonly: false,
              },
              cookie_table: [ // list of all expected cookies
                {
                  col1: '^_ga', // match all cookies starting with "_ga"
                  col2: 'google.com',
                  col3: '2 years',
                  col4: 'description ...',
                  is_regex: true,
                },
                {
                  col1: '_gid',
                  col2: 'google.com',
                  col3: '1 day',
                  col4: 'description ...',
                },
              ],
            }, {
              title: 'Advertisement and Targeting cookies',
              description: 'These cookies collect information about how you use the website, which pages you visited and which links you clicked on. All of the data is anonymized and cannot be used to identify you',
              toggle: {
                value: 'targeting',
                enabled: false,
                readonly: false,
              },
            }, {
              title: 'More information',
              description: 'For any queries in relation to our policy on cookies and your choices, please <a class="cc-link" href="#yourcontactpage">contact us</a>.',
            },
          ],
        },
      },
    },
  });

  document.addEventListener('DOMContentLoaded', () => {
    const openBannerButton = document.createElement('button');
    openBannerButton.innerText = Joomla.Text._('COM_COOKIEMANAGER_PREVIEW_BUTTON_TEXT');
    openBannerButton.id = 'open-consent-banner';
    openBannerButton.dataset.cc = 'c-settings';
    // openBannerButton.setAttribute('data-cc', 'c-settings');
    openBannerButton.className = 'preview btn btn-info';
    document.body.appendChild(openBannerButton);
  });
})(document);
