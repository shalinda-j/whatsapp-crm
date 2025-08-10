<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json");

include("../include/conn.php");
include("../include/function.php");

$response = [
  "message_code" => 200,
  "data" => [
    "key" => "",
    "promotion" => [
      "day" => "06",
      "slider" => [
        [
          "link" => "",
          "image" => ""
        ]
      ],
      "title" => "WhatsApp CRM",
      "code" => "DW-API"
    ],
    "expired" => [
      "image" => "https://crm.dw-api.com/assets/images/expired_plan.png",
      "title" => "Renove sua licença",
      "description" => "Sua licença expirou? No se preocupe, renove facilmente e volte a aproveitar todos os benefícios. Mantenha seu acesso ativo e evite interrupções no seu serviço. Clique agora para renovar e continuar sem problemas!",
      "button" => [
        "link" => "https://dropestore.com/renew",
        "title" => "Renovar licença"
      ]
    ],
    "default_image" => [
      "default_image_user" => "https://crm.dw-api.com/assets/images/extension/icon.png",
      "default_image_group" => "https://crm.dw-api.com/assets/images/extension/icon.png"
    ],
    "system_icons" => [
      "icon_search" => ""
    ],
    "global" => [
      "color_ligth_primary" => "#04d79e",
      "color_dark_primary" => "#04d79e"
    ],
    "logo" => [
      "data" => [
        "logo_image" => "https://crm.dw-api.com/assets/images/extension/icon.png",
        "logo_link" => [
          "title" => "DROPE",
          "url" => "558294229991",
          "target" => ""
        ]
      ],
      "style" => [
        "logo_background_ligth" => "#ffffff",
        "logo_background_dark" => "#222e35"
      ]
    ],
    "buttons" => [
      "data" => [
        [
          "id" => "sending_messages",
          "title" => "⚡📢 Transmissões Rápidas",
          "sub_title" => "Transmissões rápidas e de emergência",
          "icon" => "https://crm.dw-api.com/assets/images/extension/mensagens.gif",
          "help" => "<p>.</p>\n"
        ],
        [
          "id" => "webhook",
          "title" => "Webhooks de saída",
          "sub_title" => "Conecte-se com o mundo exterior",
          "icon" => "https://crm.dw-api.com/assets/images/extension/webhook.gif",
          "help" => "<p>.</p>\n"
        ],
        [
          "id" => "lists",
          "title" => "🏷️🔖 Abas Personalizadas",
          "sub_title" => "Combine suas etiquetas e organize como um profissional",
          "icon" => "https://crm.dw-api.com/assets/images/extension/abas.gif",
          "help" => "<p>.</p>\n"
        ],
        [
          "id" => "templates",
          "title" => "📃 Modelos",
          "sub_title" => "Crie e organize seus modelos",
          "icon" => "https://crm.dw-api.com/assets/images/extension/templates.gif",
          "help" => "<p>.</p>\n"
        ],
        [
          "id" => "workflow",
          "title" => "🤖 Chatbots",
          "sub_title" => "Crie e personalize seu chatbot de resposta automática",
          "icon" => "https://crm.dw-api.com/assets/images/extension/chatbot.gif",
          "help" => "<p>.</p>\n"
        ],
        [
          "id" => "schedule_messages",
          "title" => "👨‍💻 Transmissões Programadas",
          "sub_title" => "Agende todas as suas mensagens",
          "icon" => "https://crm.dw-api.com/assets/images/extension/transmissoes.gif",
          "help" => "<p>.</p>\n"
        ],
        [
          "id" => "scheduled_shipments",
          "title" => "🗓️⏰ Notificações Programadas",
          "sub_title" => "Notifique um cliente no futuro",
          "icon" => "https://crm.dw-api.com/assets/images/extension/notificacoes.gif",
          "help" => "<p>.</p>\n"
        ],
        [
          "id" => "quick_reply",
          "title" => "🗒️ Resposta Rápida",
          "sub_title" => "Responda rapidamente",
          "icon" => "https://crm.dw-api.com/assets/images/extension/replys.gif",
          "help" => "<p>.</p>\n"
        ],
        [
          "id" => "kanban_board",
          "title" => "🗃️📋 Quadro Kanban",
          "sub_title" => "Organize todos os seus clientes com esta ferramenta poderosa",
          "icon" => "https://crm.dw-api.com/assets/images/extension/kanban.gif",
          "help" => "<p>.</p>\n"
        ],
        [
          "id" => "functions",
          "title" => "🕹️ Funções",
          "sub_title" => "Importe e exporte contatos, links personalizados e muito mais...",
          "icon" => "https://crm.dw-api.com/assets/images/extension/funcoes.gif",
          "help" => "<p>.</p>\n"
        ],
        [
          "id" => "tools_free",
          "title" => "Ferramentas",
          "sub_title" => "Conjuntos de funções gratuitas",
          "icon" => "https://crm.dw-api.com/assets/images/extension/ferramentas.gif",
          "help" => "<p>.</p>\n"
        ],
        [
          "id" => "user",
          "title" => "🗣️✨ User",
          "sub_title" => "Seu painel de usuário privado",
          "icon" => "https://crm.dw-api.com/assets/images/extension/user.gif",
          "help" => "<p>.</p>\n"
        ]
      ],
      "styles" => [
        "btn_background_ligth" => "#ffffff",
        "btn_icon_background_ligth" => "#ffffff",
        "btn_text_ligth" => "#04d79e",
        "btn_background_dark" => "#ffffff",
        "btn_icon_background_dark" => "#202c33",
        "btn_text_dark" => "#04d79e"
      ]
    ],
    "loading" => [
      "loading_image" => "https://crm.dw-api.com/assets/images/load.svg",
      "loading_title" => "",
      "loading_title_description" => '
      <div class="subtext">Essa extensão foi criada utilizando o <a class="destaque">WhatsApp CRM</a></div>
      <div class="subtext">Aguarde um pouco, estamos carregando os componentes...</div><br>
        <a href="https://dropestore.com/downloads/whatsapp-crm/" class="btn-extension">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M7 17L17 7M17 7H8M17 7V16" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        Tenha sua própria extensão
      </a>
      ',
      "loading_button" => "<p>Fechar</p>"
    ],
    "table_price" => [
      [
        "id" => 1,
        "url" => "https://api.whatsapp.com/send?phone=558294229991",
        "title" => "WhatsApp CRM - Trial",
        "description" => "<strong>A melhor ferramenta de marketing e gestão do WhatsApp</strong>",
        "features" => [
          "Automação de mensagens<br>",
          "Envio ilimitado de mensagens<br>",
          "Funcionalidade de marcação e segmentação de contatos<br>",
          "Gerenciamento de contatos ilimitado<br>",
          "Histórico completo de conversas<br>",
          "Reports e análises detalhados<br>",
          "Integração avançada com o WhatsApp<br>"
        ],
        "regular_price" => "<span class=\"woocommerce-Price-amount amount\"><bdi>GRÁTIS</bdi></span>",
        "old_price" => null,
        "currency_symbol" => "BRL",
        "subscription_time" => "1 mês",
        "variations" => []
      ],
      [
        "id" => 2,
        "url" => "https://api.whatsapp.com/send?phone=558294229991",
        "title" => "WhatsApp CRM - Premium",
        "description" => "",
        "features" => [
          "Automação de mensagens<br>",
          "Envio ilimitado de mensagens<br>",
          "Funcionalidade de marcação e segmentação de contatos<br>",
          "Gerenciamento de contatos ilimitado<br>",
          "Histórico completo de conversas<br>",
          "Reports e análises detalhados<br>",
          "Integração avançada com o WhatsApp<br>",
          "Suporte prioritário por email e WhatsApp"
        ],
        "regular_price" => "<span class=\"woocommerce-Price-amount amount\"><bdi><span class=\"woocommerce-Price-currencySymbol\">USD</span>&nbsp;Get offer</bdi></span>",
        "old_price" => null,
        "currency_symbol" => "BRL",
        "subscription_time" => "1 mês",
        "variations" => [
          [
            "name" => "mensal",
            "regular_price" => "<span class=\"woocommerce-Price-amount amount\"><bdi>R$29,90</span>",
            "old_price" => "<span class=\"woocommerce-Price-amount amount\"><bdi>R$49,90</span>"
          ],
          [
            "name" => "anual",
            "regular_price" => "<span class=\"woocommerce-Price-amount amount\"><bdi>R$290,90</span>",
            "old_price" => "<span class=\"woocommerce-Price-amount amount\"><bdi>R$590,90</span>"
          ]
        ]
      ]
    ]
  ]
];

echo json_encode($response);