/**
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

((document, Joomla) => {
  'use strict';

  if (!Joomla) {
    throw new Error('Joomla API is not properly initialised');
  }
  /* global initCookieConsent */
  const cc = initCookieConsent();

  const config = Joomla.getOptions('plg_system_cookiemanager.config');
  console.log({ config });
  const cookies = Joomla.getOptions('plg_system_cookiemanager.cookies');
  console.log({ cookies });
  const categories = Joomla.getOptions('plg_system_cookiemanager.categories');
  console.log({ categories });
  const scripts = Joomla.getOptions('plg_system_cookiemanager.scripts');
  console.log({ scripts });

  const getBlocks = () => {
    const blocks = [];
    categories.forEach((category) => {
      const block = {
        title: category.title,
        description: category.description,
        toggle: {
          value: category.alias,
          enabled: false,
          readonly: category.necessary || false,
        },
      };
      const categoryCookies = cookies.filter((cookie) => cookie.alias === category.alias);
      if (categoryCookies.length) {
        block.cookie_table = [];
        categoryCookies.forEach((cookie) => {
          block.cookie_table.push({
            col1: cookie.cookie_name,
            col2: cookie.domain,
            col3: cookie.exp_period,
            col4: cookie.cookie_desc,
          });
        });
      }
      blocks.push(block);
    });

    return blocks;
  };

  const saveConsent = (cookie, preferences) => {
    const consentDetails = {
      uuid: cookie.consent_uuid,
      url: window.location.href,
      consent_opt_in: preferences.accepted_categories,
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
  };

  const showSettingButton = () => {
    const openSettingsButton = document.createElement('button');
    openSettingsButton.innerText = Joomla.Text._('COM_COOKIEMANAGER_PREVIEW_BUTTON_TEXT');
    openSettingsButton.id = 'open-consent-banner';
    openSettingsButton.type = 'button';
    // openBannerButton.dataset.cc = 'c-settings'; // Not working
    openSettingsButton.onclick = () => {
      cc.showSettings();
    };
    openSettingsButton.className = 'preview btn btn-info';
    document.body.appendChild(openSettingsButton);
  };

  document.addEventListener('DOMContentLoaded', () => {
    cc.run({
      current_lang: 'en',
      autoclear_cookies: true, // default: false
      page_scripts: true, // default: false
      cookie_expiration: config.expiration, // default: 182 (days)
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
        // triggered only once after user gave first permission
        console.log('onFirstAction:', { userPreferences }, { cookie });
        saveConsent(cookie, userPreferences);
      },
      onChange: (cookie, changedPreferences) => {
        // triggered on every setting change
        console.log('onChange: ', { cookie }, { changedPreferences });
        saveConsent(cookie, changedPreferences);
      },
      onAccept: (cookie) => {
        // triggered on every page after user triggered 'firstAction' event
        console.log('onAccept: ', { cookie });
        showSettingButton();
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
              ...getBlocks(),
              {
                title: 'More information',
                description: 'For any queries in relation to our policy on cookies and your choices, please <a class="cc-link" href="#yourcontactpage">contact us</a>.',
              },
            ],
          },
        },
      },
    });
  });
})(document, Joomla);
