const CONTACT_PAGE = "CONTACT_PAGE_PLACEHOLDER";
const TUTORIAL_PAGE = "TUTORIAL_PAGE_PLACEHOLDER";
const PRIVACY_PAGE = "PRIVACY_PAGE_PLACEHOLDER";
const FEATURE_REQUEST_PAGE = "FEATURE_REQUEST_PAGE_PLACEHOLDER";
const DOCUMENTATION_URL = "DOCUMENTATION_URL_PLACEHOLDER";
const TRANSMISSIONS_PAGE = "TRANSMISSIONS_PAGE_PLACEHOLDER";
const TABS_PAGE = "TABS_PAGE_PLACEHOLDER";
const TEMPLATES_PAGE = "TEMPLATES_PAGE_PLACEHOLDER";
const CHATBOT_PAGE = "CHATBOT_PAGE_PLACEHOLDER";
const SCHEDULE_BROADCAST_PAGE = "SCHEDULE_BROADCAST_PAGE_PLACEHOLDER";
const SCHEDULE_NOTIFICATIONS_PAGE = "SCHEDULE_NOTIFICATIONS_PAGE_PLACEHOLDER";
const RAPID_RESPONSE_PAGE = "RAPID_RESPONSE_PAGE_PLACEHOLDER";
const KANBAN_PAGE = "KANBAN_PAGE_PLACEHOLDER";
const BLUR_PAGE = "BLUR_PAGE_PLACEHOLDER";
const LINK_GENERATOR_PAGE = "LINK_GENERATOR_PAGE_PLACEHOLDER";
const IMPORT_EXPORT_PAGE = "IMPORT_EXPORT_PAGE_PLACEHOLDER";
const GOOGLE_LOGIN_PAGE = "GOOGLE_LOGIN_PAGE_PLACEHOLDER";
const GOOGLE_CALENDAR_PAGE = "GOOGLE_CALENDAR_PAGE_PLACEHOLDER";
const REMINDERS_PAGE = "REMINDERS_PAGE_PLACEHOLDER";
const ONE_MONTH_LINK = "ONE_MONTH_LINK_PLACEHOLDER";
const TWELVE_MONTH_LINK = "TWELVE_MONTH_LINK_PLACEHOLDER";
const SUPPORT_NUMBER = "SUPPORT_NUMBER_PLACEHOLDER";
const TEXT_SUPPORT_MESSAGE = "TEXT_SUPPORT_MESSAGE_PLACEHOLDER";

const translations = {
  "Perto": "Fechar",
  "nenhuma gravação encontrada": "Nenhum dado encontrado",
  "Close": "Fechar",
  "Fluxo de trabalho": "Chatbot",
  "fluxo de trabalho": "chatbot",
  "Adicionar Workflow": "Adicionar Chatbot",
  "Execução do workflow": "Execução do chatbot",
  "Digite o nome do workflow": "Digite o nome do chatbot",
  "Status do workflow": "Status do chatbot",
  "Nome do workflow": "Nome do chatbot",
  "status": "Status",
  "webhookUrl": "URL Webhook",
  "webhook": "Webhook",
  "Extrovertida": "Outgoing",
  "Procure aqui": "Pesquisar",
  "Sobre": "Ativada",
  "whatsapp link Gerador": "Gerador de links WhatsApp",
  "Conversar Agora": "Conversar",
  "Adicionar Fluxo de trabalho": "Adicionar",
  "Adicione a guia": "Adicionar",
  "Adicione webhook de saída": "Adicionar",
  "Adicionar Modelos": "Adicionar",
  "Adicionar transmissões agendadas": "Adicionar",
  "Adicionar Cronograma": "Adicionar",
  "Adicionar Resposta rápida": "Adicionar",
  "Sort": "Ordem",
  "Inicie o chat": "Inicie uma conversa",
  "Publicar": "POST",
  "Excluir": "DELETE",
  "Correção": "PATCH",
  "Colocar": "PUT"
};

function normalizeText(text) {
  return text.replace(/\s+/g, ' ').replace(/\u00A0/g, ' ').trim();
}

function safeIdleCallback(callback) {
  if ('requestIdleCallback' in window) {
    requestIdleCallback(callback, { timeout: 500 });
  } else {
    setTimeout(callback, 200);
  }
}

function updateLinks(context = document) {
  context.querySelectorAll('a.custom-anchor-text:not([data-processed])').forEach(anchor => {
    const text = anchor.textContent.trim();
    if (/Recurso de solicitação|Request feature|Demande de demande|Solicitar función/.test(text)) anchor.href = FEATURE_REQUEST_PAGE;
    else if (/Veja o tutorial|Watch tutorial|Regarder le didacticiel|Ver el tutorial/.test(text)) anchor.href = TUTORIAL_PAGE;
    else if (/Política de Privacidade|Privacy policy|Politique de confidentialité|Política de privacidad/.test(text)) anchor.href = PRIVACY_PAGE;
    anchor.dataset.processed = "true";
  });

  context.querySelectorAll('a.header-information:not([data-processed]), a.header-information-google:not([data-processed])').forEach(icon => {
    const href = icon.getAttribute('href');
    if (href.includes("/Broadcast")) icon.href = TRANSMISSIONS_PAGE;
    else if (href.includes("/tab")) icon.href = TABS_PAGE;
    else if (href.includes("/Templates")) icon.href = TEMPLATES_PAGE;
    else if (href.includes("/workflow")) icon.href = CHATBOT_PAGE;
    else if (href.includes("/Schedulebroadcast")) icon.href = SCHEDULE_BROADCAST_PAGE;
    else if (href.includes("/schedules")) icon.href = SCHEDULE_NOTIFICATIONS_PAGE;
    else if (href.includes("/Rapidresponce")) icon.href = RAPID_RESPONSE_PAGE;
    else if (href.includes("/kanban")) icon.href = KANBAN_PAGE;
    else if (href.includes("/blur")) icon.href = BLUR_PAGE;
    else if (href.includes("/linkgenerate")) icon.href = LINK_GENERATOR_PAGE;
    else if (href.includes("/import-export")) icon.href = IMPORT_EXPORT_PAGE;
    else if (href.includes("/calendar")) icon.href = GOOGLE_CALENDAR_PAGE;
    else if (href.includes("/AddGoogle")) icon.href = GOOGLE_LOGIN_PAGE;
    else if (href.includes("/reminders")) icon.href = REMINDERS_PAGE;
    icon.dataset.processed = "true";
  });

  const docButton = context.querySelector('a.custom-document-btn');
  if (docButton && !docButton.dataset.processed) {
    docButton.href = DOCUMENTATION_URL;
    docButton.dataset.processed = "true";
  }

  context.querySelectorAll('a[href^="https://dw-api.com/extension-1-month"]').forEach(a => a.href = ONE_MONTH_LINK);
  context.querySelectorAll('a[href^="https://dw-api.com/extension-12-months"]').forEach(a => a.href = TWELVE_MONTH_LINK);
}

function translateElements(context = document) {
  const selectors = [
    'button span', '.ant-btn span', 'label span', '.ant-typography',
    '.ant-form-item-label label', '.ant-collapse-header-text', '.ant-select-selection-item',
    '.ant-select-selection-placeholder', '.fs-custom-modal-header', '.ant-switch-inner',
    '.ant-checkbox-wrapper span:last-child', '.ant-radio-wrapper span:last-child',
    '.ant-empty-description', '.header-label', 'th.ant-table-cell', '.ant-tabs-tab-btn',
    'ant-table-column-title', '.ant-modal-title'
  ];

  context.querySelectorAll(selectors.join(',')).forEach(el => {
    if (el.dataset.translated) return;
    const text = normalizeText(el.textContent);
    const translated = translations[text];
    if (translated) {
      el.textContent = translated;
      el.dataset.translated = "true";
    }

    if (el.classList.contains('ant-modal-title')) {
      for (const node of el.childNodes) {
        if (node.nodeType === Node.TEXT_NODE) {
          const original = normalizeText(node.nodeValue);
          if (translations[original]) {
            node.nodeValue = translations[original] + ' ';
          }
        }
      }
    }
  });

  context.querySelectorAll('input[placeholder], textarea[placeholder]').forEach(el => {
    if (el.dataset.translated || !el.closest('[class*="custom"]')) return;
    const ph = normalizeText(el.getAttribute('placeholder') || '');
    const match = translations[ph];
    if (match) {
      el.setAttribute('placeholder', match);
      el.dataset.translated = "true";
    }
  });
}

function removeContents(context = document) {
  context.querySelectorAll('label.ant-checkbox-wrapper').forEach(label => {
    if (label.textContent.includes("Execução do webhook")) label.style.display = "none";
  });

  context.querySelectorAll('label.ant-radio-wrapper').forEach(label => {
    if (label.textContent.includes("Executar funil para palavras-chave selecionadas")) label.style.display = "none";
  });

  context.querySelectorAll('.ant-form-item').forEach(item => {
    const label = item.querySelector('label');
    if (label && label.textContent.includes("Executar funil para palavras-chave selecionadas")) {
      item.style.display = "none";
    }
  });

  const rightButtons = document.querySelector('.right_buttons-side');
  if (rightButtons) {
    rightButtons.querySelectorAll('button, .ant-space-item').forEach(el => {
      el.style.visibility = 'hidden';
      el.style.pointerEvents = 'none';
    });
  }
}

function replaceNumbers() {
    const oldNumber = "5582994229991";
    const regex = new RegExp(oldNumber, 'g');

    document.querySelectorAll("*").forEach(el => {
        if (el.childNodes.length) {
            el.childNodes.forEach(node => {
                if (node.nodeType === Node.TEXT_NODE && node.nodeValue.includes(oldNumber)) {
                    node.nodeValue = node.nodeValue.replace(regex, SUPPORT_NUMBER);
                }
            });
        }
        for (let attr of el.attributes || []) {
            if (attr.value.includes(oldNumber)) {
                el.setAttribute(attr.name, attr.value.replace(regex, SUPPORT_NUMBER));
            }
        }
    });

    document.querySelectorAll("a[href*='wa.me']").forEach(link => {
        if (link.href.includes(oldNumber)) {
            let newHref = link.href.replace(regex, SUPPORT_NUMBER);

            newHref = newHref.replace(/(\?|&)?text=[^&]*/g, '');

            newHref = newHref.replace(/[&?]$/, '');

            if (newHref.includes('?')) {
                newHref += `&text=${TEXT_SUPPORT_MESSAGE}`;
            } else {
                newHref += `?text=${TEXT_SUPPORT_MESSAGE}`;
            }

            link.href = newHref;
        }
    });

    document.querySelectorAll("button, a").forEach(btn => {
        if (btn.dataset) {
            for (let key in btn.dataset) {
                if (btn.dataset[key].includes(oldNumber)) {
                    btn.dataset[key] = btn.dataset[key].replace(regex, SUPPORT_NUMBER);
                }
            }
        }
        if (btn.onclick && btn.onclick.toString().includes(oldNumber)) {
            btn.onclick = Function("return " + btn.onclick.toString().replace(regex, SUPPORT_NUMBER))();
        }
    });
}

function monitorDOMChanges() {
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.addedNodes.length) {
                replaceNumbers();
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === Node.ELEMENT_NODE && node.tagName === 'SCRIPT' && node.src.includes('injectScript.bundle')) {
                        node.addEventListener('load', () => {
                            replaceNumbers();
                        });
                    }
                });
            }
        });
    });
    observer.observe(document.body, { childList: true, subtree: true });
}

let throttleTimer = null;
const unifiedObserver = new MutationObserver((mutations) => {
  if (throttleTimer) return;

  throttleTimer = setTimeout(() => {
    safeIdleCallback(() => {
      updateLinks();
      translateElements();
      removeContents();
      replaceNumbers();
      throttleTimer = null;
    });

    mutations.forEach(mutation => {
      mutation.addedNodes.forEach(node => {
        if (node.nodeType === Node.ELEMENT_NODE && node.tagName === 'SCRIPT' && node.src.includes('injectScript.bundle')) {
          node.addEventListener('load', () => {
            replaceNumbers();
          });
        }
      });
    });
  }, 300);
});

unifiedObserver.observe(document.body, { childList: true, subtree: true });

safeIdleCallback(() => {
  updateLinks();
  translateElements();
  removeContents();
  replaceNumbers();
});

(function injectStyle() {
  const style = document.createElement('style');
  style.textContent = `
    .custom-start-chat-icon-color {
      fill: #000 !important;
    }
    a.btn-extension,
    a.btn-extension *,
    a.btn-extension:hover,
    a.btn-extension:hover * {
      text-decoration: none !important;
      color: #fff !important;
    }
    .custom-select-webhook-radio-button-section {
      display: none !important;
    }
    .modal-description {
      text-align: initial !important;
    }
    .x1m2oepg:hover {
      background-color: transparent !important;
    }
    .xfn3atn {
      background-color: transparent !important;
    }
    button.x1c4vz4f.x2lah0s.xdl72j9.x1heor9g.xmper1u.x100vrsf.x1vqgdyp.x78zum5.xl56j7k.x6s0dn4 {
      color: #000 !important;
      background-color: transparent !important;
    }
  `;
  document.head.appendChild(style);
})();