=== videograph ===
Contributors: videograph
Tags: videos, integration, videograph, videograph.ai
Requires at least: 5.0
Tested up to: 6.6
Stable tag: 1.0
Requires PHP: 7.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Plug-and-play APIs for live streaming and on-demand video playback. Instantly publish your video content with videograph.

== Description ==

The videograph Plugin for WordPress allows you to integrate Videograph's video management capabilities into your WordPress website with ease. Streamline the process of adding, managing, and displaying videos, enhancing the user experience on your site.

== 3rd Party Service Disclosure ==

This plugin relies on the Videograph.ai service for various functionalities, including fetching video content, managing live streams, and embedding videos. Users should be aware that by using this plugin, data may be transmitted to and processed by Videograph.ai servers.

For more information about Videograph.ai, please visit [Videograph.ai website](https://www.videograph.ai/).

== Legal Information ==

- [Videograph.ai Terms of Use](https://www.videograph.ai/terms-of-use)
- [Videograph.ai Privacy Policy](https://www.videograph.ai/privacy-policy)

Key Features:
- Effortless Video Integration: With this plugin add videos to your WordPress site effortlessly using shortcodes.
- Customization Options: Tailor the appearance of videos using customizable width, height, and other parameters within the shortcode.
- Live Streaming: You can start live streaming in your WordPress site with the Videograph WP plugin.
- Streamlined Management: Easily manage your Videograph videos directly from the WordPress dashboard.
- Enhanced User Experience: Deliver an exceptional video viewing experience to your audience with the help of Videograph's advanced video technology.

== Installation ==

1. **Download the Plugin:**
   Download the latest release by either clicking "Code" above and then downloading a .zip of this repo or clicking "Releases" on the right and downloading the .zip file.

2. **Upload the Plugin:**
   Upload the downloaded .zip file on your WordPress plugins admin page, just like any other plugin. This repo contains all the necessary dependencies.

3. **Activate the Plugin:**
   Once uploaded, activate the plugin through the 'Plugins' menu in WordPress.

4. **Configure API Key:**
   Head to the plugin's settings page under the Videograph item on the left-hand menu and enter your API keys.

5. **Start Using:**
   That's it! You're now ready to upload videos and start embedding them into your pages and posts.

== Frequently Asked Questions ==

= What is Videograph? =
Videograph is a cutting-edge video encoding, streaming, and analysis platform that offers advanced features such as video encoding, video analytics, and intelligent video processing. This plugin allows seamless integration of Videograph's capabilities into your WordPress website.

= How do I use this plugin to display videos? =
After installing and activating the plugin, you can use the provided shortcode `[videograph content_id=""]` in any post or page, specifying the video's ID. For example: `[videograph content_id="123"]`.

= How can I customize the appearance of the videos? =
You can customize the appearance of the videos by adjusting the width, height, and other parameters within the shortcode. For instance, you can set the width and height using `[videograph_video id="123" width="640" height="360"]`.

= Can I manage my videos directly from WordPress? =
Absolutely! This plugin enables you to manage your Videograph.ai videos directly from your WordPress dashboard, providing a streamlined and convenient experience.

= Where can I get my API keys for Videograph.ai integration? =
To integrate Videograph with this plugin, you'll need API keys including an Access Token and a Secret Key. Obtain these keys by signing up on Videograph and following their provided documentation. To know more about generating API keys, [click here](https://docs.videograph.ai/docs/authentication-authorization).

= How can I add new videos using this plugin? =
The plugin offers an "Add New Video" page where you can input video URLs to add new videos to your WordPress site. Visit the "Add New Video" section in the plugin menu and follow the instructions.

= What do I do if I encounter issues with the plugin? =
If you face any problems or have queries regarding the plugin, you can reach out to our support team for assistance. Contact details can be found on the plugin's settings page in the WordPress dashboard. Reach out to our support team at support@videograph.ai

== Screenshots ==

1. ![Settings Page](https://videograph.ai/wp-content/uploads/2024/03/settings-page-screenshot.png)
Settings Page: This screenshot showcases the settings page of the plugin where users can configure their API keys and other options required for integration with Videograph.ai.

2. ![Add New Video](https://videograph.ai/wp-content/uploads/2024/03/add-new-video-screenshot.png)
Add New Video: This screenshot demonstrates the interface for adding a new video to the WordPress site using the plugin. Users can input video URLs and other details to upload videos seamlessly.

3. ![Library Page](https://videograph.ai/wp-content/uploads/2024/03/library-page-screenshot.png)
Library Page: This screenshot illustrates the library page of the plugin where users can manage their video library, including viewing, editing, and deleting videos. It provides an organized overview of all videos available on the site.

4. ![Create Live Stream](https://videograph.ai/wp-content/uploads/2024/03/create-live-stream-screenshot.png)
Create Live Stream: This screenshot displays the interface for creating a live stream directly from the WordPress dashboard using the plugin. Users can specify stream settings and initiate live streaming effortlessly.

5. ![Live Streams Page](https://videograph.ai/wp-content/uploads/2024/03/live-streams-page-screenshot.png)
Live Streams Page: This screenshot presents the live streams page of the plugin where users can monitor and manage their ongoing live streams. It provides real-time information and controls for monitoring stream health and viewer engagement.

6. ![Live Recordings Page](https://videograph.ai/wp-content/uploads/2024/03/live-recordings-page-screenshot.png)
Live Recordings Page: This screenshot showcases the live recordings page of the plugin where users can access recordings of past live streams. It offers playback options and additional details about each recording for reference and analysis.

== Changelog ==

= 1.0 =
* Initial release of the Videograph plugin.
* Added integration with Videograph.ai service for video management.
* Implemented shortcode for effortless video embedding.
* Provided settings page for configuring API keys and other options.

== Other Notes ==

For more information, visit [Videograph](https://videograph.ai/).
**Logo:** ![Plugin Logo](https://www.videograph.ai/wp-content/uploads/2022/07/videograph.png)
**Icon:** ![Plugin Icon](https://www.videograph.ai/wp-content/uploads/2024/03/icon.svg)
**Icon:** ![Plugin Icon 128x128](https://www.videograph.ai/wp-content/uploads/2024/03/icon-128x128.png)
**Icon:** ![Plugin Icon 256x256](https://www.videograph.ai/wp-content/uploads/2024/03/icon-256x256.png)
**Banner:** ![Plugin Banner](https://www.videograph.ai/wp-content/uploads/2024/03/banner-772x250.jpg)

== Credits ==

* Credits: [Videograph](https://videograph.ai)

== License ==

This plugin is licensed under the GPL-2.0-or-later or later.

`<?php code(); ?>`
