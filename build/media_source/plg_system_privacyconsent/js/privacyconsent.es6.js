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

  const config = Joomla.getOptions('plg_system_privacyconsent.config');
  console.log({ config });
  const cookies = Joomla.getOptions('plg_system_privacyconsent.cookies');
  console.log({ cookies });
  const categories = Joomla.getOptions('plg_system_privacyconsent.categories');
  console.log({ categories });
  const scripts = Joomla.getOptions('plg_system_privacyconsent.scripts');
  console.log({ scripts });

  const primaryButtonLabel = config.consent_modal.primary_button_role === 'accept_all'
    ? Joomla.Text._('PLG_SYSTEM_PRIVACYCONSENT_BANNER_BTN_ACCEPT_ALL')
    : Joomla.Text._('PLG_SYSTEM_PRIVACYCONSENT_BANNER_BTN_ACCEPT_SELECTED');
  const secondaryButtonLabel = config.consent_modal.secondary_button_role === 'accept_necessary'
    ? Joomla.Text._('PLG_SYSTEM_PRIVACYCONSENT_BANNER_BTN_REJECT_ALL')
    : Joomla.Text._('PLG_SYSTEM_PRIVACYCONSENT_BANNER_BTN_SETTINGS');

  const getBlocks = () => {
    const blocks = [];
    categories.forEach((category) => {
      const params = JSON.parse(category.params);
      const mandatory = params.mandatory && params.mandatory === '1';
      const block = {
        title: category.title,
        description: category.description,
        toggle: {
          value: category.alias,
          enabled: mandatory,
          readonly: mandatory,
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
      url: `index.php?option=com_ajax&plugin=privacyconsent&group=system&format=json&data=${JSON.stringify(consentDetails)}`,
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
    openSettingsButton.innerText = Joomla.Text._('COM_PRIVACY_PREVIEW_BUTTON_TEXT');
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
      autoclear_cookies: true,
      page_scripts: true,
      cookie_expiration: config.expiration,
      cookie_name: 'jcookie',
      mode: config.mode,
      delay: config.delay,
      force_consent: config.force_consent === '1',
      hide_from_bots: config.hide_from_bots === '1',
      remove_cookie_tables: config.remove_cookie_tables === '1',
      cookie_same_site: config.cookie_same_site,
      gui_options: {
        consent_modal: {
          layout: config.consent_modal.layout,
          position: `${config.consent_modal.position_y} ${config.consent_modal.position_x}`,
          transition: config.consent_modal.transition,
          swap_buttons: config.consent_modal.swap_buttons === '1',
        },
        settings_modal: {
          layout: config.settings_modal.layout,
          position: config.settings_modal.position,
          transition: config.settings_modal.transition,
        },
      },
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
            title: Joomla.Text._('PLG_SYSTEM_PRIVACYCONSENT_BANNER_TITLE'),
            description: Joomla.Text._('PLG_SYSTEM_PRIVACYCONSENT_BANNER_DESC'),
            primary_btn: {
              text: primaryButtonLabel,
              role: config.consent_modal.primary_button_role,
            },
            secondary_btn: {
              text: secondaryButtonLabel,
              role: config.consent_modal.secondary_button_role,
            },
          },
          settings_modal: {
            title: Joomla.Text._('PLG_SYSTEM_PRIVACYCONSENT_SETTINGS_TITLE'),
            save_settings_btn: Joomla.Text._('PLG_SYSTEM_PRIVACYCONSENT_SETTINGS_BTN_SAVE'),
            accept_all_btn: Joomla.Text._('PLG_SYSTEM_PRIVACYCONSENT_SETTINGS_BTN_ACCEPT_ALL'),
            reject_all_btn: Joomla.Text._('PLG_SYSTEM_PRIVACYCONSENT_SETTINGS_BTN_REJECT_ALL'),
            close_btn_label: Joomla.Text._('PLG_SYSTEM_PRIVACYCONSENT_SETTINGS_BTN_CLOSE'),
            cookie_table_headers: [
              { col1: Joomla.Text._('PLG_SYSTEM_PRIVACYCONSENT_TABLE_HEADERS_COL1') },
              { col2: Joomla.Text._('PLG_SYSTEM_PRIVACYCONSENT_TABLE_HEADERS_COL2') },
              { col3: Joomla.Text._('PLG_SYSTEM_PRIVACYCONSENT_TABLE_HEADERS_COL3') },
              { col4: Joomla.Text._('PLG_SYSTEM_PRIVACYCONSENT_TABLE_HEADERS_COL4') },
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
