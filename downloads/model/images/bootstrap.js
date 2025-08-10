const SETTINGS = {
  API_ENDPOINT: "ENDPOINT_API_URL",
  SITE_URL: "URL_SITE"
};

let data = null;
let data_license = null;
let data_user_info = null;
let validate = false;
let currentLang;

function yt(e, t) {
  return new Promise((r) => {
    const n = {};
    (n[e] = t),
      chrome.storage.local.set(n, function () {
        r();
      });
  });
}
async function getSelectedLanguage() {
  const result = await chrome.storage.local.get("SELECTED_LANGUAGE");
  if (!result.SELECTED_LANGUAGE) {
    yt("SELECTED_LANGUAGE", "pt");
  }
  return result.SELECTED_LANGUAGE || "pt";
}

// Usage
getSelectedLanguage().then((lang) => {
  currentLang = lang;
  const storedData = localStorage.getItem("mbf_data")
    ? JSON.parse(atob(localStorage.getItem("mbf_data")))
    : null;

  data_license = {
    phone: storedData ? storedData.phone : null,
    unique_id: storedData ? storedData.unique_code : null,
    license: storedData ? storedData.license : null,
  };
  //------------------------Obteniendo datos del servidor
  fixModals();

  appendCSS();

  fetchData("", data_license.phone).then((info) => {
    data = info.data;
    removeIncesary();
    loadComplete(async () => {
      let loadingElement = document.querySelector("#mbf_loading");

      if (!data_license.phone || !data_license.unique_id) {
        location.reload();
      }

      addSidebar();
      if (data_license.license && data_license.license !== "") {
        const result = await validateLicense(
          data_license.unique_id,
          data_license.phone,
          data_license.license
        );

        if (result.success) {
          validate = true;
          data_user_info = result.data;

          window.postMessage(
            {
              type: "ON_FETCH_REMOTE_DATA",
              response: "",
              extraData: { type: "validate_device", mbf: true },
            },
            "*"
          );
        } else {
          if (storedData && storedData.license) {
            delete storedData.license;
            localStorage.setItem("mbf_data", btoa(JSON.stringify(storedData)));
          }
          openModalExpired();
        }
      }
      loadingElement.remove();
      document.querySelector(".main_toolbar").style.opacity = 1;
    });
  });

  //--------------------Detectar si est conectado

  var isUserLoggedIn = setInterval(function () {
    if (Boolean(document.getElementById("pane-side")) && data) {
      clearInterval(isUserLoggedIn);
      openLoading();
    }
  }, 50);

  //----------------------Mostrar la ventana de cargando

  function openLoading() {
    let html = `
    <div id="mbf_loading">
	<div>
      <h1>${data.loading.loading_title}</h1>
      <img src="${data.loading.loading_image}" alt="Loading" />
      <p>
        ${data.loading.loading_title_description}
      </p>
	   </div>
    </div>
    `;

    document.body.insertAdjacentHTML("beforeend", html);
  }

  //----------------------- Agregar botones

  function addSidebar() {
    var ifInsertButtons = setInterval(function () {
      if (Boolean(document.querySelector(".two._aigs"))) {
        clearInterval(ifInsertButtons);

        const app = document.querySelector("._aigs > header");

        const sidebarHTML = `
      <div class="mbf-sidebar">
        <a class="logo" href="${SETTINGS.SITE_URL}" target="_blank">
            <img src="${data.logo.data.logo_image}" alt="Logo">
        </a>
        <div class="mbf-buttons">
          ${data.buttons.data
            .map(
              (button) => `
            <div id="${button.id}" class="btn-item">
                <div class="btn-item-img">
                    <img class="wc-user-img" src="${button.icon}">
                </div>
                <div class="btn-item-info">
                    <strong class="btn-item-info-title">${button.title}</strong>
                    <p class="btn-item-info-subtitle">${button.sub_title}</p>
                </div>
            </div>
          `
            )
            .join("")}
        </div>
      </div>
    `;

        app.insertAdjacentHTML("afterbegin", sidebarHTML);

        const items = [
          { id: "sending_messages", index: 1 },
          { id: "lists", index: 2 },
          { id: "templates", index: 3 },
          { id: "workflow", index: 4 },
          { id: "schedule_messages", index: 5 },
          { id: "webhook", index: 6 },
          { id: "scheduled_shipments", index: 7 },
          { id: "quick_reply", index: 8 },
          { id: "kanban_board", index: 9 },
          { id: "functions", index: 10 },
          { id: "tools_free", index: 11 },
          { id: "user", index: 12 },
        ];

        items.forEach((item) => {
          const element = document.querySelector(`#${item.id}`);
          if (!element) return;

          element.addEventListener("click", () => {
          let menu;

          if (validate === false && item.index == 11) {
            menu = document.querySelector(
              `.main_toolbar > .right_buttons-side > div > div > .ant-space-item:nth-child(2)`
            );
            if (menu) menu.querySelector("button")?.click();
          } else if (validate === false && item.index == 12) {
            menu = document.querySelector(
              `.main_toolbar > .right_buttons-side > div > div > .ant-space-item:nth-child(3)`
            );
            if (menu) menu.querySelector("button")?.click();
          } else if (validate === false && item.index !== 11 && item.index !== 12) {
            openModalKey();
          } else {
            menu = document.querySelector(
              `.main_toolbar > .right_buttons-side > div > div > .ant-space-item:nth-child(${item.index})`
            );
            if (menu) menu.querySelector("button")?.click();
          }
        });
        });
      }
    }, 100);
  }

  //----------------------- Load complete function

  function loadComplete(callback) {
    const observer = new MutationObserver(function (mutationsList) {
      mutationsList.forEach(function (mutation) {
        if (mutation.type === "childList") {
          mutation.addedNodes.forEach(function (node) {
            if (node.classList && node.classList.contains("main_toolbar")) {
              observer.disconnect();
              node.style.opacity = 0;

              let intervalId;

              intervalId = setInterval(() => {
                const logoElement = node.querySelector(
                  ".custom-product-logo-layout"
                );
                const leftTabElement = node.querySelector(".left_tab-side");
                const rightButtonsElement = node.querySelector(
                  ".right_buttons-side"
                );

                if (logoElement && leftTabElement && rightButtonsElement) {
                  clearInterval(intervalId); // Clear the interval
                  if (typeof callback === "function") {
                    callback();
                  }
                }
              }, 100);
            }
          });
        }
      });
    });

    const config = { childList: true, subtree: true };
    observer.observe(document.body, config);
  }

  //----------------------- Fetch data function

  async function fetchData(key = "", phone) {
    const ajaxUrl = new URL(
      SETTINGS.API_ENDPOINT + "api/panouncement.php"
    );
    ajaxUrl.searchParams.append("action", "get_data");

    if (key) {
      ajaxUrl.searchParams.append("key", key);
    }

    if (lang) {
      ajaxUrl.searchParams.append("lang", lang);
    }

    try {
      const response = await fetch(ajaxUrl, {
        method: "GET",
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();
      return data;
    } catch (error) {
      console.error("Error:", error);
      throw error;
    }
  }

  //-------------------  Abrir el modal licencia expirada

  // Set your current language using a two‐letter code
  // Options: "en" (English), "es" (Spanish), "pt" (Portuguese), "fr" (French)
  const SELECTED_LANGUAGE = lang;

  // Translation texts for the modals
  const translations = {
    en: {
      modalLicense: {
        title: "License Details",
        description:
          "Here you will find detailed information about your license and options to uninstall it if necessary.",
        table: {
          user: "User:",
          phone: "Phone:",
          license: "License:",
          plan: "Plan:",
          next_payment: "Next Payment:",
        },
        buttons: {
          uninstall: "Uninstall License",
          close: "Close",
        },
      },
      modalExpired: {
        description:
          "Your license has expired. Renew now to continue enjoying uninterrupted service.",
        button: {
          title: "Renew License",
          // Assuming data.expired.button.link is available from your data object.
          // Otherwise, you can hardcode a link here.
        },
        close: "Close",
      },
      modalKey: {
        title: "You must enter a key",
        description:
          "Please enter your license key in the field below to activate advanced features.",
        button: "Validate",
        empty_error: "You must enter your license",
        success:
          'Hello {user}, your license for the "{plan}" plan has been successfully activated. Your next payment is scheduled for {date}. Thank you for trusting us!',
      },
      modalPrice: {
        title: "Choose Your Plan",
        description:
          'If you already have a license, click here to access: <a href="#" class="open-insert-key">Enter key</a>',
        toggle: {
          month: "Month",
          year: "Year",
        },
        buy: "Buy",
        close: "Close",
      },
    },
    es: {
      modalLicense: {
        title: "Detalles de la Licencia",
        description:
          "Aquí encontrarás información detallada sobre tu licencia y opciones para desinstalarla, si es necesario.",
        table: {
          user: "Usuario:",
          phone: "Teléfono:",
          license: "Licencia:",
          plan: "Plan:",
          next_payment: "Próximo pago:",
        },
        buttons: {
          uninstall: "Desinstalar licencia",
          close: "Cerrar",
        },
      },
      modalExpired: {
        description:
          "Tu licencia ha expirado. Renueva ahora para continuar disfrutando del servicio sin interrupciones.",
        button: {
          title: "Renovar licencia",
        },
        close: "Cerrar",
      },
      modalKey: {
        title: "Debes ingresar una clave",
        description:
          "Debes ingresar tu licencia en el siguiente campo para activar las funciones avanzadas.",
        button: "Validar",
        empty_error: "Debes ingresar tu licencia",
        success:
          'Hola {user}, tu licencia del plan "{plan}" se ha activado correctamente. Tu próximo pago está programado para {date}. ¡Gracias por confiar en nosotros!',
      },
      modalPrice: {
        title: "Elige tu Plan",
        description:
          'Si ya tienes una licencia, haz clic aquí para acceder: <a href="#" class="open-insert-key">Insertar clave</a>',
        toggle: {
          month: "Mes",
          year: "Año",
        },
        buy: "Comprar",
        close: "Cerrar",
      },
    },
    pt: {
      modalLicense: {
        title: "Detalhes da licença",
        description:
          "Aqui você encontrará informações detalhadas sobre sua licença e opções para desinstalá-la, se necessário.",
        table: {
          user: "Usuário",
          phone: "Telefone",
          license: "Licença",
          plan: "Plano",
          next_payment: "Próximo pagamento",
        },
        buttons: {
          uninstall: "Remover licença",
          close: "Fechar",
        },
      },
      modalExpired: {
        description:
          "Sua licença expirou. Renove agora para continuar aproveitando o serviço sem interrupções.",
        button: {
          title: "Renovar licença",
        },
        close: "Fechar",
      },
      modalKey: {
        title: "Você deve inserir uma licença",
        description:
          "Você deve inserir sua licença no campo a seguir para ativar as funções avançadas.",
        button: "Validar",
        empty_error: "Você deve inserir sua licença",
        success:
          'Olá {user}, sua licença do plano "{plan}" foi ativada com sucesso. Seu próximo pagamento está programado para {date}. Obrigado por confiar em nós!',
      },
      modalPrice: {
        title: "Escolha seu Plano",
        description:
          'Se você já tem uma licença, clique <a href="#" class="open-insert-key">aqui</a> para ativar.',
        toggle: {
          month: "Mês",
          year: "Ano",
        },
        buy: "Comprar",
        close: "Fechar",
      },
    },
    fr: {
      modalLicense: {
        title: "Détails de la Licence",
        description:
          "Ici, vous trouverez des informations détaillées sur votre licence et des options pour la désinstaller si nécessaire.",
        table: {
          user: "Utilisateur:",
          phone: "Téléphone:",
          license: "Licence:",
          plan: "Plan:",
          next_payment: "Prochain paiement:",
        },
        buttons: {
          uninstall: "Désinstaller la licence",
          close: "Fermer",
        },
      },
      modalExpired: {
        description:
          "Votre licence a expiré. Renouvelez-la dès maintenant pour continuer à profiter d’un service ininterrompu.",
        button: {
          title: "Renouveler la licence",
        },
        close: "Fermer",
      },
      modalKey: {
        title: "Vous devez entrer une clé",
        description:
          "Veuillez saisir votre licence dans le champ ci-dessous pour activer les fonctionnalités avancées.",
        button: "Valider",
        empty_error: "Vous devez entrer votre licence",
        success:
          'Bonjour {user}, votre licence pour le plan "{plan}" a été activée avec succès. Votre prochain paiement est prévu pour le {date}. Merci de votre confiance!',
      },
      modalPrice: {
        title: "Choisissez votre Plan",
        description:
          'Si vous possédez déjà une licence, cliquez ici pour y accéder: <a href="#" class="open-insert-key">Entrer la clé</a>',
        toggle: {
          month: "Mois",
          year: "Année",
        },
        buy: "Acheter",
        close: "Fermer",
      },
    },
  };

  //================================================================
  // Function to open the license details modal
  //================================================================
  function openModalLicense() {
    const html = `
  <div id="modalLicense" class="modal">
    <div class="modal-content">
      <button class="close"></button>
      <!-- Start - Modal content -->
      <h1 class="modal-title">${translations[SELECTED_LANGUAGE].modalLicense.title}</h1>
      <p class="modal-description">${translations[SELECTED_LANGUAGE].modalLicense.description}</p>

      <table class="user-info-table">
        <tbody>
          <tr>
            <td><strong>${translations[SELECTED_LANGUAGE].modalLicense.table.user}</strong></td>
            <td>${data_user_info.userDeviceData.device_data.userName}</td>
          </tr>
          <tr>
            <td><strong>${translations[SELECTED_LANGUAGE].modalLicense.table.phone}</strong></td>
            <td>${data_user_info.userDeviceData.device_data.skd_wa_no}</td>
          </tr>
          <tr>
            <td><strong>${translations[SELECTED_LANGUAGE].modalLicense.table.license}</strong></td>
            <td>${data_user_info.userDeviceData.skey}</td>
          </tr>
          <tr>
            <td><strong>${translations[SELECTED_LANGUAGE].modalLicense.table.plan}</strong></td>
            <td>${data_user_info.userDeviceData.plan_type}</td>
          </tr>
          <tr>
            <td><strong>${translations[SELECTED_LANGUAGE].modalLicense.table.next_payment}</strong></td>
            <td>${data_user_info.userDeviceData.validate.end_date}</td>
          </tr>
        </tbody>
      </table>
      <div class="modal-buttons" style="margin-top: 20px;">
        <button type="button" class="uninstall-button mbf_button">${translations[SELECTED_LANGUAGE].modalLicense.buttons.uninstall}</button>
        <button type="button" class="close-button mbf_button alternative">${translations[SELECTED_LANGUAGE].modalLicense.buttons.close}
</button>
      </div>
      <!-- End - Modal content -->
    </div>
  </div>
  `;

    // Close any existing modals before opening the new one.
    document
      .querySelectorAll(".mbf-ant-modal-close")
      .forEach((item) => item.click());
    document.body.insertAdjacentHTML("beforeend", html);

    const modal = document.getElementById("modalLicense");
    const modalClose = modal.querySelector(".close");
    const button_uninstall = modal.querySelector(".uninstall-button");
    const button_close = modal.querySelector(".close-button");

    modal.style.display = "flex";

    modalClose.onclick = () => {
      modal.style.display = "none";
      modal.remove();
    };
    button_close.onclick = () => {
      modal.style.display = "none";
      modal.remove();
    };

    button_uninstall.onclick = async () => {
      modal.classList.add("loading");
      const result = await uninstallLicense(data_license.license);

      if (result.success) {
        validate = false;
        localStorage.removeItem("mbf_data");
        alert(result.message);
        setTimeout(() => {
          location.reload();
        }, 500);
      } else {
        modal.classList.remove("loading");
        alert(result.message);
        return false;
      }
    };

    window.onclick = (event) => {
      if (event.target === modal) {
        modal.style.display = "none";
        modal.remove();
      }
    };
  }

  //================================================================
  // Function to open the expired license modal
  //================================================================
  function openModalExpired() {
    const html = `
  <div id="modalPrice" class="modal">
    <div class="modal-content">
      <button class="close">${translations[SELECTED_LANGUAGE].modalExpired.close}</button>
      <!-- Start - Modal content -->
      <div class="expired_description">${data.expired.description}</div>
      <a href="${data.expired.button.link}" target="_blank" class="btn-swal expired_button swal2-styled swal2-default-outline">
        ${translations[SELECTED_LANGUAGE].modalExpired.button.title}
      </a>
      <div class="modal-buttons">
        <button type="button" class="expired_button mbf_button">${translations[SELECTED_LANGUAGE].modalExpired.close}</button>
      </div>
      <!-- End - Modal content -->
    </div>
  </div>
  `;

    document.body.insertAdjacentHTML("beforeend", html);
    const modal = document.getElementById("modalPrice");
    const modalClose = modal.querySelector(".close");
    const button = modal.querySelector(".mbf_button");

    modal.style.display = "flex";
    modalClose.onclick = () => {
      modal.style.display = "none";
      modal.remove();
    };
    button.onclick = () => {
      modal.style.display = "none";
      modal.remove();
    };
    window.onclick = (event) => {
      if (event.target === modal) {
        modal.style.display = "none";
        modal.remove();
      }
    };
  }

  //================================================================
  // Function to open the license key modal
  //================================================================
  function openModalKey() {
    const html = `
<div id="modalPrice" class="modal">
  <div class="modal-content">
    <button class="close"></button>
    <!-- Start - Modal content -->
    <h1 class="modal-title">${translations[SELECTED_LANGUAGE].modalKey.title}</h1>
    <div class="modal-description">
      ${translations[SELECTED_LANGUAGE].modalKey.description}
    </div>
    <div class="toggle-price">
      <input type="text" class="mbf_input_text" style="margin-bottom:20px;"/>
      <div class="mbf_button">${translations[SELECTED_LANGUAGE].modalKey.button}</div>
    </div>
    <!-- End - Modal content -->
  </div>
</div>
  `;

    document.body.insertAdjacentHTML("beforeend", html);
    const modal = document.getElementById("modalPrice");
    const modalClose = modal.querySelector(".close");

    modal.style.display = "flex";
    modalClose.onclick = () => {
      modal.style.display = "none";
      modal.remove();
    };
    window.onclick = (event) => {
      if (event.target === modal) {
        modal.style.display = "none";
        modal.remove();
      }
    };

    const input = modal.querySelector(".mbf_input_text");
    const button = modal.querySelector(".mbf_button");

    button.addEventListener("click", async () => {
      if (input.value === "") {
        alert(translations[SELECTED_LANGUAGE].modalKey.empty_error);
      } else {
        modal.classList.add("loading");
        const result = await validateLicense(
          data_license.unique_id,
          data_license.phone,
          input.value
        );

        if (result.success) {
          modal.classList.remove("loading");
          window.postMessage(
            {
              type: "ON_FETCH_REMOTE_DATA",
              response: "",
              extraData: { type: "validate_device", mbf: true },
            },
            "*"
          );
          modal.style.display = "none";
          modal.remove();
          validate = true;
          data_user_info = result.data;
          storeLicenseKey(input.value);

          window.postMessage(
            {
              type: "REQUEST_STORE_S_DATA_WTTH_KEY",
              key: "WR1Y5A_LICENCE_KEY",
              data: input.value,
            },
            "*"
          );

          let successMsg = translations[SELECTED_LANGUAGE].modalKey.success;
          successMsg = successMsg
            .replace(
              "{user}",
              data_user_info.userDeviceData.device_data.skd_wa_no
            )
            .replace("{plan}", data_user_info.userDeviceData.plan_type)
            .replace("{date}", data_user_info.userDeviceData.validate.end_date);
          alert(successMsg);
        } else {
          modal.classList.remove("loading");
          input.value = "";
          input.focus();
          alert(result.message);
        }
      }
    });
  }

  //================================================================
  // Function to open the subscription (price) modal
  //================================================================
  function openModalPrice() {
    const html = `
<div id="modalPrice" class="modal">
  <div class="modal-content">
    <button class="close"></button>
    <!-- Start - Modal content -->
    <h1 class="modal-title">${
      translations[SELECTED_LANGUAGE].modalPrice.title
    }</h1>
    <div class="modal-description">
      ${translations[SELECTED_LANGUAGE].modalPrice.description}
    </div>
    <div class="toggle-price">
      <input type="checkbox" id="toggle" class="toggle-checkbox" />
      <label for="toggle" class="toggle-container">
        <div>${translations[SELECTED_LANGUAGE].modalPrice.toggle.month}</div> 
        <div>${translations[SELECTED_LANGUAGE].modalPrice.toggle.year}</div>
      </label>
    </div>
    <div class="carousel-container">
      <button class="carousel-btn left-btn">←</button>
      <ul>
        ${data.table_price
          .map((item) => {
            return `<li class="subscription"> 
                      <div class="subscription_header">
                        <h2 class="subscription_header_title">${item.title}</h2>
                        ${
                          item.description === "-"
                            ? `<div class="subscription_header_description">${item.description}</div>`
                            : ""
                        }
                      </div>
                      <div class="subscription_content">
                        <ul class="subscription_feature">
                          ${item.features.join("")}
                        </ul>
                        <div class="subscription_price">
                          <span class="subscription_price_old">*</span>
                          <span class="subscription_price_current">*</span>
                        </div>
                      </div>
                      <div class="subscription_footer">
                        <a class="mbf_button" href="${
                          item.url
                        }" target="_blank">${
              translations[SELECTED_LANGUAGE].modalPrice.buy
            }</a>
                      </div>
                    </li>`;
          })
          .join("")}
      </ul>
      <button class="carousel-btn right-btn">→</button>
    </div>
    <!-- End - Modal content -->
  </div>
</div>
  `;

    document.body.insertAdjacentHTML("beforeend", html);
    const modal = document.getElementById("modalPrice");
    const modalClose = modal.querySelector(".close");

    modal.style.display = "flex";
    modalClose.onclick = () => {
      modal.style.display = "none";
      modal.remove();
    };
    window.onclick = (event) => {
      if (event.target === modal) {
        modal.style.display = "none";
        modal.remove();
      }
    };

    const carousel = modal.querySelector(".carousel-container ul");
    const leftBtn = modal.querySelector(".carousel-container .left-btn");
    const rightBtn = modal.querySelector(".carousel-container .right-btn");
    const items = modal.querySelectorAll(".carousel-container li.subscription");
    const check = modal.querySelector(".toggle-price input");
    const openInsertKeyLink = modal.querySelector(".open-insert-key");

    function getCurrentIndex() {
      const itemWidth = items[0].clientWidth;
      const scrollLeft = carousel.scrollLeft;
      return Math.round(scrollLeft / itemWidth);
    }

    leftBtn.addEventListener("click", () => {
      carousel.scrollLeft -= carousel.clientWidth;
      setTimeout(get_price, 100);
    });

    rightBtn.addEventListener("click", () => {
      carousel.scrollLeft += carousel.clientWidth;
      setTimeout(get_price, 100);
    });

    check.addEventListener("change", get_price);

    if (openInsertKeyLink) {
      openInsertKeyLink.addEventListener("click", (e) => {
        e.preventDefault();
        modal.style.display = "none";
        modal.remove();
        openModalKey();
      });
    }

    function get_price() {
      const currentIndex = getCurrentIndex();
      const slide = carousel.children[currentIndex];
      let info = data.table_price[currentIndex];

      if (info.variations.length === 0) {
        modal.querySelector(".toggle-price").classList.add("disabled");
        if (info.old_price === null) {
          slide.querySelector(".subscription_price_old").style.display = "none";
          slide.querySelector(".subscription_price_current").innerHTML =
            info.regular_price;
        } else {
          slide.querySelector(".subscription_price_old").style.display =
            "inline";
          slide.querySelector(".subscription_price_current").style.display =
            "inline";
          slide.querySelector(".subscription_price_old").innerHTML =
            info.old_price;
          slide.querySelector(".subscription_price_current").innerHTML =
            info.regular_price;
        }
      } else {
        modal.querySelector(".toggle-price").classList.remove("disabled");
        if (check.checked) {
          if (info.variations[1].old_price === null) {
            slide.querySelector(".subscription_price_old").style.display =
              "none";
            slide.querySelector(".subscription_price_current").innerHTML =
              info.variations[1].regular_price;
          } else {
            slide.querySelector(".subscription_price_old").style.display =
              "inline";
            slide.querySelector(".subscription_price_old").innerHTML =
              info.variations[1].old_price;
            slide.querySelector(".subscription_price_current").innerHTML =
              info.variations[1].regular_price;
          }
        } else {
          if (info.variations[0].old_price === null) {
            slide.querySelector(".subscription_price_old").style.display =
              "none";
            slide.querySelector(".subscription_price_current").innerHTML =
              info.variations[0].regular_price;
          } else {
            slide.querySelector(".subscription_price_old").style.display =
              "inline";
            slide.querySelector(".subscription_price_old").innerHTML =
              info.variations[0].old_price;
            slide.querySelector(".subscription_price_current").innerHTML =
              info.variations[0].regular_price;
          }
        }
      }
    }

    get_price();
  }

  //------------Validar Licencia
  
  async function validateLicense(unique_id, phone, license) {
    const params = new URLSearchParams({
      unique_id: unique_id,
      phone: phone,
      license: license,
    }).toString();

    // Use the configured API endpoint from the generated extension
    const base = SETTINGS.API_ENDPOINT.endsWith("/")
      ? SETTINGS.API_ENDPOINT
      : SETTINGS.API_ENDPOINT + "/";
    const url = `${base}api/pvalidate.php?${params}`;

    try {
      const response = await fetch(url, { method: "GET" });
      const res = await response.json();

      if (res.status === 200) {
        return { success: true, message: res.message, data: res.dData };
      } else {
        return { success: false, message: res.message };
      }
    } catch (error) {
      return { success: false, message: "Error de red o servidor" };
    }
  }

  //----------------------Almacenar la licencia

  function storeLicenseKey(licenseValue) {
    const localStorageKey = "mbf_data";

    function encryptData(data) {
      return btoa(JSON.stringify(data));
    }

    function decryptData(encryptedData) {
      return JSON.parse(atob(encryptedData));
    }

    let storedData = null;

    try {
      const encryptedData = localStorage.getItem(localStorageKey);
      storedData = encryptedData ? decryptData(encryptedData) : null;
    } catch (e) {}

    if (!storedData) {
      storedData = {
        license: licenseValue,
      };
    } else {
      storedData.license = licenseValue;
    }

    try {
      localStorage.setItem(localStorageKey, encryptData(storedData));
    } catch (e) {}
  }

  //----------------------- Fix Modals

  function fixModals() {
    let button = `<button type="button" aria-label="Close" class="mbf-ant-modal-close"><span class="ant-modal-close-x"><span role="img" aria-label="close" class="anticon anticon-close ant-modal-close-icon"><svg fill-rule="evenodd" viewBox="64 64 896 896" focusable="false" data-icon="close" width="1em" height="1em" fill="currentColor" aria-hidden="true"><path d="M799.86 166.31c.02 0 .04.02.08.06l57.69 57.7c.04.03.05.05.06.08a.12.12 0 010 .06c0 .03-.02.05-.06.09L569.93 512l287.7 287.7c.04.04.05.06.06.09a.12.12 0 010 .07c0 .02-.02.04-.06.08l-57.7 57.69c-.03.04-.05.05-.07.06a.12.12 0 01-.07 0c-.03 0-.05-.02-.09-.06L512 569.93l-287.7 287.7c-.04.04-.06.05-.09.06a.12.12 0 01-.07 0c-.02 0-.04-.02-.08-.06l-57.69-57.7c-.04-.03-.05-.05-.06-.07a.12.12 0 010-.07c0-.03.02-.05.06-.09L454.07 512l-287.7-287.7c-.04-.04-.05-.06-.06-.09a.12.12 0 010-.07c0-.02.02-.04.06-.08l57.7-57.69c.03-.04.05-.05.07-.06a.12.12 0 01.07 0c.03 0 .05.02.09.06L512 454.07l287.7-287.7c.04-.04.06-.05.09-.06a.12.12 0 01.07 0z"></path></svg></span></span></button>`;

    const observer1 = new MutationObserver(() => {
      const customSettingMenu = document.querySelector(
        ".ant-popover-inner-content .custom-setting-menu"
      );
      if (customSettingMenu) {
        observer1.disconnect();

        const element =
          customSettingMenu.closest(".ant-popover").parentElement.parentElement;

        let popover = element.querySelector(".ant-popover");

        element
          .querySelector(".ant-popover-content")
          .insertAdjacentHTML("afterbegin", button);

        element
          .querySelector(".mbf-ant-modal-close")
          .addEventListener("click", () => {
            popover.classList.add("ant-popover-hidden");
          });

        element.classList.add("full-screen");

        const headerDiv = element.querySelector(
          ".custom-setting-menu-header div"
        );

        headerDiv.innerHTML = "";
        headerDiv.innerHTML = `<p>${
          data_user_info?.userDeviceData?.device_data?.skd_wa_no ||
          data_license.phone
        }</p>`;

        const menuItemLicense = document.querySelector(
          ".custom-setting-menu-items-layout .custom-setting-menu-item:nth-child(1)"
        );
        if (validate == false) {
          const settings = document.querySelector(
            ".custom-setting-menu-items-layout .custom-setting-menu-item:nth-child(5)"
          );
          const newSettings = settings.cloneNode(true);
          settings.replaceWith(newSettings);

          newSettings.addEventListener("click", function () {
            if (validate == false) {
              openModalKey();
            }
          });
        }
        const newMenuItemLicense = menuItemLicense.cloneNode(true);
        menuItemLicense.replaceWith(newMenuItemLicense);
        newMenuItemLicense.addEventListener("click", function () {
          if (validate == true) {
            openModalLicense();
          } else {
            openModalKey();
          }
        });
      }
    });

    // Crear un MutationObserver
    const observer2 = new MutationObserver(() => {
      const customSettingMenu = document.querySelector(
        ".ant-popover-inner-content .custom-advance-tools-menu"
      );
      if (customSettingMenu) {
        observer2.disconnect();

        const element =
          customSettingMenu.closest(".ant-popover").parentElement.parentElement;
        let popover = element.querySelector(".ant-popover");

        element
          .querySelector(".ant-popover-content")
          .insertAdjacentHTML("afterbegin", button);

        element
          .querySelector(".mbf-ant-modal-close")
          .addEventListener("click", () => {
            popover.classList.add("ant-popover-hidden");
          });

        element.classList.add("full-screen");
      }
    });

    observer2.observe(document.body, { childList: true, subtree: true });

    observer1.observe(document.body, { childList: true, subtree: true });
  }

  // Eliminar cosas innesesarias------------------------

  function removeIncesary() {
    const observer = new MutationObserver((mutationsList) => {
      for (let mutation of mutationsList) {
        if (mutation.type === "childList") {
          const targetElementLogoTag = document.querySelector(
            'img[src="https://crm.dw-api.com/images/assets/icon.webp"]'
          );
          if (targetElementLogoTag) {
            targetElementLogoTag.src = data.logo.data.logo_image;
            const parentElementLogoTag = targetElementLogoTag.parentElement;

            if (parentElementLogoTag) {
              parentElementLogoTag.classList.add("wraper-logo-tag");
            }
          }

          const targetElement = document.querySelector(
            ".ant-modal-root ul.ant-rate"
          );
          if (targetElement) {
            const modalRoot = document.querySelector(".ant-modal-root");
            if (modalRoot) {
              modalRoot.remove();
            }
          }
        }
      }
    });

    // Observa los cambios en todo el body
    observer.observe(document.body, { childList: true, subtree: true });
  }

  // Injectar Estilos -------------------------------------
  function appendCSS() {
    const styleElement = document.createElement("style");
    styleElement.id = "mbf-custom-style";
    document.body.appendChild(styleElement);

    styleElement.textContent = `

  
/*Inicio - Etiqeutas --------------------*/


span.top-tabbar-badge-class {
    background:var(--custom-primary-color)!important;
    color:white; 
}

.dark span.top-tabbar-badge-class {
    background: white !important;
    color:black; 
}




#main_section .main_toolbar .left_tab-side .ant-tabs-tab[data-node-key]:hover:after {
  background:var(--custom-primary-color) !important;
  border: 1px solid var(--custom-primary-color) !important;
}

/*Final - Etiqeutas --------------------*/

`;
  }

  //Desintalar lisencia -------------------------------------

  async function uninstallLicense(license) {
    try {
      const res = {
        status: 200,
        message: "Licença removida",
      };

      window.postMessage(
        {
          type: "REQUEST_STORE_S_DATA_WTTH_KEY",
          key: "WR1Y5A_LICENCE_KEY",
          data: "",
        },
        "*"
      );

      if (res.status === 200) {
        return { success: true, message: res.message };
      } else {
        return { success: false, message: res.message };
      }
    } catch (error) {
      return { success: false, message: "Error de red o servidor" };
    }
  }
});

chrome.storage.onChanged.addListener((changes, areaName) => {
  if (changes.SELECTED_LANGUAGE.newValue) {
    window.location.reload();
  }
});