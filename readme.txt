=== OmniConva AI Sales Agent Connector ===
Contributors: naminbd, ok9xnirab
Tags: ai chatbot, live chat, woocommerce, chatbot, lead generation
Requires at least: 4.5
Tested up to: 7.1
Requires PHP: 7.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add an AI sales agent chatbot to WordPress and WooCommerce. It answers shoppers, captures leads and helps customers place orders in live chat, 24/7.

== Description ==

**OmniConva AI Sales Agent Connector** adds the OmniConva AI chatbot to your WordPress or WooCommerce store. Paste your Site Key and an AI sales agent appears on every page. It answers product questions, recommends products, collects leads and helps shoppers finish their orders, all from the chat window.

Most website visitors leave without asking a question. An AI sales assistant replies straight away, at any hour, in a friendly chat widget, so fewer visitors leave and more of them become customers.

= Why use an AI sales agent on your WordPress site? =

* **Replies 24/7.** The AI chatbot answers pre-sales questions about products, pricing, shipping and returns, even when your team is offline.
* **More sales.** The agent guides shoppers from their first question to checkout and can help a customer place an order in the chat.
* **Lead capture.** An optional pre-chat form collects each visitor's name, email address and phone number before the conversation starts.
* **Human handoff.** Your team can take over a conversation, and the visitor sees clearly when a human agent is replying.
* **Knows returning customers.** Logged-in WordPress and WooCommerce customers are identified securely, so the AI agent can greet them by name and help with their account.

= Live chat features =

* Modern chat widget that works on mobile and desktop
* Replies stream in word by word
* Photo, video and file attachments, with drag and drop
* Voice messages recorded in the browser
* Emoji picker and message reactions
* Quote a message to reply to it
* Conversation starters and quick-reply buttons
* Proactive messages that start the conversation for you
* Conversation history, so visitors can pick up where they left off
* Thumbs-up/down feedback on messages and a star rating when the chat ends (CSAT)
* Formatted replies with lists, links and tables

= Built for WooCommerce stores =

The plugin works on any WordPress site and is especially useful for WooCommerce. When a customer is logged in, the plugin sends their name, email address, avatar and WooCommerce billing phone and address to the AI agent. The AI sales agent can then answer order questions and help with purchases without asking the customer to type their details again.

Customer identity is signed on your server with HMAC-SHA256 using your private Identity Secret. The secret stays on your server and is never sent to the browser, so visitors cannot pretend to be another customer.

= Lightweight and simple =

* Setup takes about a minute: paste your Site Key and save. You don't need to write any code.
* A single script loads in the footer, and only after you have added a Site Key.
* No new database tables, and no slowdown on your WordPress admin screens.
* Works with any theme and page builder.

= How it works =

1. Create an OmniConva account and set up your AI sales agent: its knowledge, tone, products and branding.
2. Copy the Site Key from your OmniConva dashboard.
3. Install this plugin and paste the key under **Settings → OmniConva AI Sales Agent**.
4. The AI chat widget now appears on your site.

You manage the AI agent's training, conversations, leads, human handoff and analytics in the OmniConva dashboard.

== External services ==

This plugin connects your site to the **OmniConva AI Sales Agent** service. The service runs the AI chatbot and stores conversations. The plugin cannot do anything without the service, and you need an OmniConva account and Site Key to use it.

**When data is sent:** only on the public side of your site, and only after you have saved a Site Key. Nothing is sent from your WordPress admin screens.

**What data is sent:**

* Your Site Key, used to load your widget's settings.
* Chat messages, attachments and voice messages that visitors send.
* Contact details a visitor enters in the pre-chat form, such as name, email address and phone number.
* Page and browser information: the current page URL and title, the referring page, recently viewed pages on your site, browser language and user agent.
* For logged-in users, if you have set an Identity Secret: the user ID, display name, email address, avatar URL, WooCommerce billing phone and billing address, and a signature (HMAC hash) of the user ID. The Identity Secret itself is never sent.

The widget also keeps a visitor ID, a session token and a short browsing history in the visitor's browser (localStorage) so conversations continue from one page to the next.

This service is provided by OmniConva:

* Terms of Service: https://omniconva.com/terms
* Privacy Policy: https://omniconva.com/privacy

Please mention this service in your site's privacy policy.

== Installation ==

= Automatic installation =

1. In your WordPress admin, go to **Plugins → Add New**.
2. Search for **OmniConva AI Sales Agent Connector**.
3. Click **Install Now**, then **Activate**.

= Manual installation =

1. Download the plugin zip file.
2. Go to **Plugins → Add New → Upload Plugin** and upload the zip, or extract it into `/wp-content/plugins/`.
3. Activate the plugin from the **Plugins** screen.

= Setup =

1. Go to **Settings → OmniConva AI Sales Agent**, or click **Settings** on the plugin's row on the Plugins screen.
2. Paste the **Site Key** from your OmniConva dashboard.
3. Optional: paste your **Identity Secret** to recognize logged-in customers.
4. Optional: tick **Credit link** to show a "Powered by OmniConva" link in the widget. It is off by default.
5. Click **Save Changes** and visit your site to see the AI chat widget.

== Frequently Asked Questions ==

= Do I need an OmniConva account? =

Yes. The plugin connects your site to the OmniConva AI Sales Agent service. You need an OmniConva account to get a Site Key, and to train and manage your AI agent.

= Does it work with WooCommerce? =

Yes. The AI chatbot works on any WordPress site, and it works especially well on WooCommerce stores. When you set an Identity Secret, logged-in customers are recognized automatically along with their WooCommerce billing details, so the AI agent can help with orders without asking for them again.

= Can the AI chatbot take orders? =

Yes. The AI sales agent can guide a shopper through choosing products and help them place an order in the chat, as you configure in your OmniConva dashboard.

= Can a human take over the chat? =

Yes. Your team can join any conversation from the OmniConva dashboard. The widget shows the visitor when a human agent is replying.

= Will the chat widget slow down my website? =

No. The plugin loads one script in the footer, and only after a Site Key is saved. It adds no database tables and loads nothing on your admin screens.

= Does it work with my theme or page builder? =

Yes. The widget floats on top of your pages, so it works with block themes, classic themes, Elementor, Divi and other page builders.

= What is the Identity Secret for? =

The Identity Secret lets the AI agent securely recognize logged-in users. The plugin uses it on your server to sign each user's identity, and the secret is never sent to the browser. Leave it empty if you only want to chat with anonymous visitors.

= Is it GDPR friendly? =

The plugin sends data to OmniConva only after you add a Site Key, and it never sends your Identity Secret. See the **External services** section for exactly what is shared, and mention OmniConva in your privacy policy. How conversation data is stored and handled is covered by OmniConva's Privacy Policy.

= Can visitors send images, files or voice messages? =

Yes. Visitors can attach photos, videos and files, or record a voice message in the browser.

= Does the widget add a link to OmniConva on my site? =

Only if you want it to. A "Powered by OmniConva" link in the chat widget is off by default. You can turn it on under **Settings → OmniConva AI Sales Agent → Credit link**.

= Where do I see conversations and leads? =

All conversations, captured leads, ratings and feedback appear in your OmniConva dashboard.

== Screenshots ==

1. The plugin settings page: paste your Site Key and optional Identity Secret to connect your site to OmniConva.
2. The AI chat widget on the frontend, answering a shopper's questions in real time.
3. The AI sales agent helping a customer place a WooCommerce order in the chat.

== Changelog ==

= 1.0.0 =
* Initial release.
* Settings page with Site Key and Identity Secret.
* AI sales agent chat widget on the frontend.
* Signed identity (HMAC-SHA256) for logged-in WordPress and WooCommerce customers.

== Upgrade Notice ==

= 1.0.0 =
Initial release of the OmniConva AI Sales Agent Connector.
