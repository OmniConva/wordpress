=== OmniConva AI Sales Agent Connector ===
Contributors: naminbd, ok9xnirab
Tags: ai chatbot, live chat, woocommerce, chatbot, lead generation
Requires at least: 4.5
Tested up to: 7.1
Requires PHP: 7.0
Stable tag: 1.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add an AI sales agent chatbot to WordPress and WooCommerce. It answers shoppers, captures leads and helps customers place orders in live chat, 24/7.

== Description ==

**OmniConva AI Sales Agent Connector** adds the OmniConva AI chatbot to your WordPress or WooCommerce store. Click Connect, approve the site in OmniConva, and an AI sales agent appears on every page. It answers product questions, recommends products, collects leads and helps shoppers finish their orders, all from the chat window.

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

* Setup takes about a minute: click **Connect to OmniConva**, sign in or create a free account, and approve. Your keys are filled in for you, and you don't need to write any code.
* A single script loads in the footer, and only after you have added a Site Key.
* No new database tables, and no slowdown on your WordPress admin screens.
* Works with any theme and page builder.

= How it works =

1. Install this plugin and open **Settings → OmniConva AI Sales Agent**.
2. Click **Connect to OmniConva**. Sign in, or create a free OmniConva account, and approve your site.
3. You are sent back to WordPress with your Site Key and Identity Secret saved, and your site's domain allowed in OmniConva.
4. The AI chat widget now appears on your site. Change the agent's name, status line, avatar shape, colors and greeting right on the same settings page, and set up its knowledge, tone and products in your OmniConva dashboard.

You manage the AI agent's training, conversations, leads, human handoff and analytics in the OmniConva dashboard.

== External services ==

This plugin connects your site to the **OmniConva AI Sales Agent** service. The service runs the AI chatbot and stores conversations. The plugin cannot do anything without the service, and you need an OmniConva account and Site Key to use it.

**When data is sent:** on the public side of your site, only after you have saved a Site Key. From your WordPress admin, only when an administrator clicks **Connect to OmniConva** or **Reconnect**, opens the plugin's settings page while connected (to load the chat widget settings), or saves the chat widget settings there.

**What the Connect button sends:** your site's address, its name (the Site Title), the admin address OmniConva sends you back to, and two random one-time values used to secure the connection. Your server then receives the Site Key and Identity Secret directly from OmniConva. No WordPress user data is sent.

**What the chat widget settings send:** your Site Key, a timestamp and a signature (HMAC hash) of the request made with your Identity Secret, and, when you save, the agent name, status text, avatar shape, colors and greeting message you entered. The Identity Secret itself is never sent. No WordPress user data is sent.

**What data is sent:**

* Your Site Key, used to load your widget's settings.
* Chat messages, attachments and voice messages that visitors send.
* Contact details a visitor enters in the pre-chat form, such as name, email address and phone number.
* Page and browser information: the current page URL and title, the referring page, recently viewed pages on your site (page addresses without their query strings), browser language, platform and user agent.
* For logged-in users, if you have set an Identity Secret: the user ID, display name, email address, avatar URL, WooCommerce billing phone and the first line of the billing address, and a signature (HMAC hash) of the user ID. The Identity Secret itself is never sent.

The widget also keeps a visitor ID, a session token and a short browsing history in the visitor's browser (localStorage) so conversations continue from one page to the next.

This service is provided by OmniConva:

* Terms of Service: https://omniconva.com/terms
* Privacy Policy: https://omniconva.com/privacy

Please mention this service in your site's privacy policy.

== Source code ==

`assets/js/widget.min.js` is built from the TypeScript source included in the plugin under `src/widget/`. To rebuild it, run `npm install` and then `npm run build` in that folder.

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

1. In WordPress, go to **Settings → OmniConva AI Sales Agent**, or click **Settings** on the plugin's row on the Plugins screen.
2. Click **Connect to OmniConva**, sign in or create an account, and approve. Only the OmniConva store owner can approve.
3. Optional: tick **Credit link** to show a "Powered by OmniConva" link in the widget, and click **Save Changes**. It is off by default.
4. Visit your site to see the AI chat widget.

To set up by hand instead (for example if your server cannot make outgoing requests): in your OmniConva dashboard, open **Settings → Custom Chat**, enter your website's domain under **Site domain**, then paste the **Site Key** and, optionally, the **Identity Secret** into the plugin's settings and save.

== Frequently Asked Questions ==

= Do I need an OmniConva account? =

Yes. The plugin connects your site to the OmniConva AI Sales Agent service. You need an OmniConva account to get a Site Key, and to train and manage your AI agent.

= Does it work with WooCommerce? =

Yes. The AI chatbot works on any WordPress site, and it works especially well on WooCommerce stores. When you set an Identity Secret, logged-in customers are recognized automatically along with their WooCommerce billing details, so the AI agent can help with orders without asking for them again.

= Can the AI chatbot take orders? =

Yes. The AI sales agent can guide a shopper through choosing products and help them place an order in the chat, as you configure in your OmniConva dashboard.

= Can a human take over the chat? =

Yes. Your team can join any conversation from the OmniConva dashboard. The widget shows the visitor when a human agent is replying.

= I use a page cache or CDN. Anything to know? =

For logged-in users the plugin puts that user's name, email, phone and a signed identity into the page, so the chat recognizes them. WordPress and the common caching plugins skip logged-in users automatically. If you cache whole pages at the edge (for example Cloudflare "Cache Everything" or Varnish), make sure pages are not cached for logged-in users — otherwise one customer's details could be served to another visitor.

= The chat widget doesn't appear on my site. What should I check? =

1. Make sure your website's domain is saved under **Settings → Custom Chat → Site domain** in your OmniConva dashboard. The widget only loads on that domain. `www.yourstore.com` and `yourstore.com` count as the same site, but any other subdomain, such as `shop.yourstore.com`, must be the one you enter.
2. Make sure the Site Key under **Settings → OmniConva AI Sales Agent** matches the one in your OmniConva dashboard, and that you clicked **Save Changes**.
3. If you use a caching or optimization plugin, clear its cache so the page includes the widget script.

= Will the chat widget slow down my website? =

No. The plugin loads one script in the footer, and only after a Site Key is saved. It adds no database tables and loads nothing on your admin screens apart from a few styles on its own settings page.

= Does it work with my theme or page builder? =

Yes. The widget floats on top of your pages, so it works with block themes, classic themes, Elementor, Divi and other page builders.

= What is the Identity Secret for? =

The Identity Secret lets the AI agent securely recognize logged-in users. The plugin uses it on your server to sign each user's identity, and the secret is never sent to the browser. Leave it empty if you only want to chat with anonymous visitors.

= Is it GDPR friendly? =

The plugin sends data to OmniConva only after you add a Site Key, or when you click Connect, and it never sends your Identity Secret. See the **External services** section for exactly what is shared, and mention OmniConva in your privacy policy. How conversation data is stored and handled is covered by OmniConva's Privacy Policy.

= Can visitors send images, files or voice messages? =

Yes. Visitors can attach photos, videos and files, or record a voice message in the browser.

= Does the widget add a link to OmniConva on my site? =

Only if you want it to. A "Powered by OmniConva" link in the chat widget is off by default. You can turn it on under **Settings → OmniConva AI Sales Agent → Credit link**.

= Can I change how the chat widget looks? =

Yes. Under **Settings → OmniConva AI Sales Agent → Chat widget** you can set the agent name, the status line under it, the avatar shape, the header and text colors, and the greeting message. The settings are saved in OmniConva, so they match the Custom Chat settings in your OmniConva dashboard. Editing them from WordPress needs the Identity Secret, which the Connect button saves for you.

= Where do I see conversations and leads? =

All conversations, captured leads, ratings and feedback appear in your OmniConva dashboard.

== Screenshots ==

1. The plugin settings page: connect your site to OmniConva in one click, or enter your Site Key and optional Identity Secret by hand.
2. The AI chat widget on the frontend, answering a shopper's questions in real time.
3. The AI sales agent helping a customer place a WooCommerce order in the chat.

== Changelog ==

= 1.0.2 =
* New: **Connect to OmniConva** button. Sign in or create a free account, approve your site, and the Site Key and Identity Secret are saved for you. No more copying and pasting.
* Connecting also allows your site's domain in OmniConva, so the widget loads without any further setup.
* The settings page shows which OmniConva store the site is connected to, with Reconnect and Disconnect buttons.
* The Site Key and Identity Secret can still be entered by hand.

= 1.0.1 =
* Security: the chat widget no longer lets a link inside a chat message add HTML attributes. Update as soon as possible.
* Messages written by a human agent are shown as plain text.
* The Site Key and Identity Secret are checked for the right shape when saved; an invalid value is refused and the saved one kept.
* The Identity Secret is no longer shown in the settings form once saved; leave the field blank to keep it, or tick "Remove" to clear it.
* The widget records page addresses without their query strings.

= 1.0.0 =
* Initial release.
* Settings page with Site Key and Identity Secret.
* AI sales agent chat widget on the frontend.
* Signed identity (HMAC-SHA256) for logged-in WordPress and WooCommerce customers.

== Upgrade Notice ==

= 1.0.2 =
Connect your site to OmniConva in one click, without copying keys.

= 1.0.1 =
Security fix in the chat widget. Update as soon as possible.

= 1.0.0 =
Initial release of the OmniConva AI Sales Agent Connector.
