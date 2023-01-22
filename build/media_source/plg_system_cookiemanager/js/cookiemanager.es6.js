/**
 * @copyright  (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

((document) => {
  'use strict';

  // Script Types
  const TYPE_SCRIPT = 1;
  const TYPE_EXTERNAL_SCRIPT = 2;
  const TYPE_IFRAME = 3;
  const TYPE_EMBED = 4;
  const TYPE_OBJECT = 5;
  const TYPE_IMG = 6;
  const TYPE_LINK = 7;

  // Positions
  const POSITION_AFTER_BEGIN_HEAD = 1;
  const POSITION_BEFORE_END_HEAD = 2;
  const POSITION_AFTER_BEGIN_BODY = 3;

  const cookies = document.cookie.split('; ');
  const config = Joomla.getOptions('config');
  const code = Joomla.getOptions('code');
  const parse = Range.prototype.createContextualFragment.bind(document.createRange());

  // Array for consents
  const consentsOptIn = [];
  const consentsOptOut = [];

  // Calculate cookie expiration period
  const getExpiration = () => {
    const exp = config.expiration;
    const d = new Date();
    d.setTime(d.getTime() + (exp * 24 * 60 * 60 * 1000));
    const expires = d.toUTCString();
    return expires;
  };

  const displayConsentData = (ccuuid, date, optIn) => {
    document.getElementById('ccuuid').innerText = ccuuid;
    document.getElementById('consent-date').innerText = date;
    document.getElementById('consent-opt-in').innerText = optIn;
  };

  const addScript = (position, script) => {
    if (position === POSITION_AFTER_BEGIN_HEAD) {
      document.head.prepend(parse(script));
    }
    if (position === POSITION_BEFORE_END_HEAD) {
      document.head.append(parse(script));
    }
    if (position === POSITION_AFTER_BEGIN_BODY) {
      document.body.prepend(parse(script));
    } else {
      document.body.append(parse(script));
    }
  };

  const addNotScript = (type, script) => {
    if (type === TYPE_OBJECT) {
      const value = script.match(/data="([^\s]*)"\s/)[1];
      const element = document.querySelector(`[data-src="${value}"]`);
      if (element) {
        element.setAttribute('data', value);
        element.removeAttribute('data-src');
      }
    }
    if (type === TYPE_LINK) {
      const value = script.match(/href="(.+)"/)[1];
      const element = document.querySelector(`[data-href="${value}"]`);
      if (element) {
        element.setAttribute('href', value);
        element.removeAttribute('data-href');
      }
    }
    if (type === TYPE_IFRAME || type === TYPE_EMBED || type === TYPE_IMG) {
      const value = script.match(/src="([^\s]*)"\s/)[1];
      const element = document.querySelector(`[data-src="${value}"]`);
      if (element) {
        element.setAttribute('src', value);
        element.removeAttribute('data-src');
      }
    }
  };

  const acceptConsent = (value, categoryKey, exp) => {
    Object.values(value).forEach((i) => {
      if (i.type === TYPE_SCRIPT || i.type === TYPE_EXTERNAL_SCRIPT) {
        addScript(i.position, i.code);
      } else {
        addNotScript(i.type, i.code);
      }

      document.cookie = `cookie_category_${categoryKey}=true; expires=${exp}; path=/; sameSite=strict;`;
    });
    consentsOptIn.push(categoryKey);
  };

  const denyConsent = (deniedConsent, exp) => {
    document.cookie = `cookie_category_${deniedConsent}=false; expires=${exp}; path=/; sameSite=strict;`;
    consentsOptOut.push(deniedConsent);
  };

  const storingConsents = () => {
    const consentsIn = consentsOptIn.join(', ');
    const consentsOut = consentsOptOut.join(', ');
    const date = new Date();
    const uuid = cookies.find((cookie) => cookie.startsWith('uuid='))?.split('=')[1];

    document.cookie = 'consents_opt_in=[]; path=/; sameSite=strict;';
    document.cookie = `consent_date=${date}; path=/; sameSite=strict;`;

    const consentDetails = {
      uuid,
      url: window.location.href,
      consent_opt_in: consentsIn,
      consent_opt_out: consentsOut,
    };
    Joomla.request({
      url: `index.php?option=com_ajax&plugin=cookiemanager&group=system&format=json&data=${JSON.stringify(consentDetails)}`,
      method: 'POST',
      onSuccess: (response) => {
        const result = JSON.parse(response);
        const ccuuid = result.data.length ? result.data[0] : '';

        document.cookie = `ccuuid=${ccuuid}; path=/; sameSite=strict;`;
        displayConsentData(ccuuid, date, consentsIn);
      },
      onError(xhr) {
        Joomla.renderMessages({ error: [xhr] }, '#system-message-container');
      },
    });
  };

  const saveChoice = (exp) => {
    document.cookie = `cookieBanner=shown; expires=${exp}; path=/; sameSite=strict;`;
    storingConsents();
  };

  const acceptChoice = () => {
    const exp = getExpiration();
    document.querySelectorAll('[data-cookiecategory]').forEach((item) => {
      const categoryKey = item.getAttribute('data-cookiecategory');

      if (item.checked) {
        Object.entries(code).forEach(([key, value]) => {
          if (key === categoryKey) {
            acceptConsent(value, categoryKey, exp);
          }
        });
      } else {
        denyConsent(categoryKey, exp);
      }
    });
    saveChoice(exp);
  };

  const acceptAllCookies = () => {
    const exp = getExpiration();
    Object.entries(code).forEach(([key, value]) => {
      acceptConsent(value, key, exp);
    });
    saveChoice(exp);
  };

  document.addEventListener('DOMContentLoaded', () => {
    document.querySelector('#consentBanner .modal-dialog').classList.add(config.position);
    document.querySelector('#settingsBanner .modal-dialog').classList.add('modal-dialog-scrollable');

    document.getElementById('confirmChoice').addEventListener('click', acceptChoice);
    document.getElementById('confirmSettingsChoice').addEventListener('click', acceptChoice);
    document.querySelectorAll('[data-button="acceptAllCookies"]').forEach((btn) => {
      btn.addEventListener('click', acceptAllCookies);
    });

    // Show cookie consent banner
    if (cookies.indexOf('cookieBanner=shown') === -1) {
      const Banner = new bootstrap.Modal(document.querySelector('#consentBanner'));
      Banner.show();
    }

    // Add consent details to the cookie settings banner
    if (cookies.find((c) => c.startsWith('consents_opt_in='))) {
      const consentOptIn = cookies.find((c) => c.startsWith('consents_opt_in=')).split('=')[1];
      const consentDate = cookies.find((c) => c.startsWith('consent_date=')).split('=')[1];
      const ccuuid = cookies.find((c) => c.startsWith('ccuuid=')).split('=')[1];

      displayConsentData(ccuuid, consentDate, consentOptIn);
    }

    // Block cookie setting code on the users' first visit
    document.querySelectorAll('[data-cookie-category]').forEach((item) => {
      const category = item.getAttribute('data-cookie-category');

      cookies.forEach((cookie) => {
        if (cookie.search(`${category}=true`) > 0) {
          item.checked = true;
        } else {
          Object.entries(code).forEach(([catAlias, categoryCookies]) => {
            if (catAlias === category) {
              Object.values(categoryCookies).forEach((categoryCookie) => {
                addNotScript(categoryCookie.type, categoryCookie.code);
              });
            }
          });
        }
      });
    });

    document.querySelectorAll('[data-cookiecategory]').forEach((item) => {
      const category = item.getAttribute('data-cookiecategory');
      cookies.forEach((cookie) => {
        if (cookie.search(`${category}=true`) > 0) {
          item.checked = true;
        }
      });
    });

    document.querySelectorAll('a[data-bs-toggle="collapse"]').forEach((item) => {
      item.addEventListener('click', () => {
        if (item.innerText === Joomla.Text._('COM_COOKIEMANAGER_PREFERENCES_MORE_BUTTON_TEXT')) {
          item.innerText = Joomla.Text._('COM_COOKIEMANAGER_PREFERENCES_LESS_BUTTON_TEXT');
        } else {
          item.innerText = Joomla.Text._('COM_COOKIEMANAGER_PREFERENCES_MORE_BUTTON_TEXT');
        }
      });
    });
  });
})(document);
