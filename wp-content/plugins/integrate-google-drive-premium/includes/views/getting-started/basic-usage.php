<div id="basic-usage" class="getting-started-content content-basic-usage">
    <div class="content-heading">
        <h2><?php esc_html_e( 'Basic Usage', 'integrate-google-drive' ); ?></h2>
        <p><?php esc_html_e( 'A Beginner\'s Guide to Efficiently Using the Plugin', 'integrate-google-drive' ); ?></p>
    </div>

    <!-- Link a Google Account -->
    <section class="section-add-account section-full">
        <div class="col-description">
            <h2><?php esc_html_e( 'Link a Google account', 'integrate-google-drive' ); ?></h2>
            <p><?php esc_html_e( 'After activating the plugin you have to link your Google account to the plugin. You can link multiple Google account to the plugin.', 'integrate-google-drive' ); ?></p>

            <h4><?php esc_html_e( 'Follow the below steps to add a Google account to the plugin:', 'integrate-google-drive' ); ?></h4>
            <ol>
                <li><?php
                    /* translators: %s: Google Drive */
                    printf( __( 'Go to the Google Drive or %1$s Google Drive > Settings %2$s page in the WordPress admin dashboard on your website.', 'integrate-google-drive' ), '<strong>', '</strong>' ); ?></li>
                <li><?php esc_html_e( 'Click on the Add account button.', 'integrate-google-drive' ); ?></li>
                <li><?php esc_html_e( 'A new window will open, you will be redirected to the Google login page to login with your email.', 'integrate-google-drive' ); ?></li>
                <li><?php esc_html_e( 'Select the email address with which you want to login.', 'integrate-google-drive' ); ?></li>
                <li><?php esc_html_e( 'Click the Allow button to authorize the plugin to access your Google Drive data.', 'integrate-google-drive' ); ?></li>
                <li><?php esc_html_e( 'Wait for the authorization process, then you are done!', 'integrate-google-drive' ); ?></li>
            </ol>
        </div>

        <div class="col-image">
            <iframe
                    src="https://www.youtube.com/embed/ZatIhm8JM2s?rel=0"
                    title="<?php esc_attr_e( 'YouTube video player', 'integrate-google-drive' ); ?>" frameBorder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowFullScreen></iframe>
        </div>

    </section>


    <!-- Shortcode Builder -->
    <section class="section-shortcode-builder section-full">
        <div class="col-description">
            <h2><?php esc_html_e( 'Shortcode Module Builder', 'integrate-google-drive' ); ?></h2>
            <p><?php
                /* translators: %s: shortcode */
                printf(__( 'You can create any numbers of shortcode module and use them in your post/ page using the %1$s[integrate_google_drive]%2$s shortcode.', 'integrate-google-drive'), '<code>', '</code>' ); ?></p>

            <h4><?php esc_html_e( 'There are several module types to create a shortcode. Those are:', 'integrate-google-drive' ); ?></h4>
            <ol>
                <li><strong><?php esc_html_e( 'File Browser -', 'integrate-google-drive' ); ?></strong>
                    - <?php esc_html_e( 'Users can browse your Google Drive files.', 'integrate-google-drive' ); ?></li>
                <li><strong><?php esc_html_e( 'Embed Document', 'integrate-google-drive' ); ?></strong>
                    - <?php esc_html_e( 'Embed any Google Drive file to your page/ post.', 'integrate-google-drive' ); ?>
                </li>
                <li><strong><?php esc_html_e( 'File Uploader', 'integrate-google-drive' ); ?></strong>
                    - <?php esc_html_e( 'Users can upload files to your Google Drive account from your website.', 'integrate-google-drive' ); ?>
                </li>
                <li><strong><?php esc_html_e( 'Photo Gallery', 'integrate-google-drive' ); ?></strong>
                    - <?php esc_html_e( 'Lightbox grid photo gallery module.', 'integrate-google-drive' ); ?></li>
                <li><strong><?php esc_html_e( 'Audio & Video Player', 'integrate-google-drive' ); ?></strong>
                    - <?php esc_html_e( 'Play audio and video in a single player using this module.', 'integrate-google-drive' ); ?>
                </li>
                <li><strong><?php esc_html_e( 'File Search', 'integrate-google-drive' ); ?></strong>
                    - <?php esc_html_e( 'Users can search any Google Drive files from your website using this module.', 'integrate-google-drive' ); ?>
                </li>
                <li><strong><?php esc_html_e( 'File View Links', 'integrate-google-drive' ); ?></strong>
                    - <?php esc_html_e( 'Insert any Google Drive file view link to yor page/ post.', 'integrate-google-drive' ); ?>
                </li>
                <li><strong><?php esc_html_e( 'File Download Links', 'integrate-google-drive' ); ?></strong>
                    - <?php esc_html_e( 'Insert any Google Drive file download link to yor page/ post', 'integrate-google-drive' ); ?>
                </li>
            </ol>
        </div>

        <div class="col-image">
            <iframe class="aspect-video rounded shadow w-[700px]"
                    src="https://www.youtube.com/embed/8M84lcvfCiI?rel=0"
                    title="<?php esc_attr_e( 'YouTube video player', 'integrate-google-drive' ); ?>" frameBorder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowFullScreen></iframe>
        </div>

    </section>

    <!-- Embed Documents -->
    <section class="section-embed section-full">
        <div class="col-description">
            <h2><?php esc_html_e( 'Embed Documents', 'integrate-google-drive' ); ?></h2>
            <p><?php esc_html_e( 'You can embed any Google Drive files to your page/ post in two ways.', 'integrate-google-drive' ); ?></p>

            <h4><?php esc_html_e( 'Those are:', 'integrate-google-drive' ); ?></h4>
            <ol>
                <li><strong><?php esc_html_e( 'Shortcode Builder', 'integrate-google-drive' ); ?></strong>
                    - <?php esc_html_e( 'To embed any documents with the shortcode builder you have to select the Embed Documents type.', 'integrate-google-drive' ); ?>
                </li>
                <li><strong><?php esc_html_e( 'Classic Editor Button', 'integrate-google-drive' ); ?></strong>
                    - <?php esc_html_e( 'You can embed your Google Drive documents using the classic editor Google Drive button while you are editing any page/ post.', 'integrate-google-drive' ); ?>
                </li>
            </ol>
        </div>

        <div class="col-image">
            <iframe
                    src="https://www.youtube.com/embed/LsXK0XWqLyI?rel=0"
                    title="<?php esc_attr_e( 'YouTube video player', 'integrate-google-drive' ); ?>" frameBorder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowFullScreen></iframe>
        </div>

    </section>

    <!--    Insert Links -->
    <section class="section-links section-full">
        <div class="col-description">
            <h2><?php esc_html_e( 'Insert Download/ View Links', 'integrate-google-drive' ); ?></h2>
            <p><?php esc_html_e( 'You can insert direct links in any page/ post to download and view any Google Drive files. Here also you can insert the direct links to download/ view using two ways. Those are:', 'integrate-google-drive' ); ?></p>

            <h4><?php esc_html_e( 'There are several module types to create a shortcode. Those are:', 'integrate-google-drive' ); ?></h4>
            <ol>
                <li><strong><?php esc_html_e( 'Shortcode Builder', 'integrate-google-drive' ); ?></strong>
                    - <?php esc_html_e( 'To insert any Google Drive files links with the shortcode builder you have to select the File Download Link/ File View Link type.', 'integrate-google-drive' ); ?>
                </li>
                <li><strong><?php esc_html_e( 'Classic Editor Button', 'integrate-google-drive' ); ?></strong>
                    - <?php esc_html_e( 'You can also insert any Google Drive files links using the Classic Editor Google Drive button while you are editing any page/ post.', 'integrate-google-drive' ); ?>
                </li>
            </ol>
        </div>

        <div class="col-image">
            <iframe
                    src="https://www.youtube.com/embed/XZh6B58F9uM?rel=0" frameBorder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowFullScreen></iframe>
        </div>

    </section>


    <!--    Private Folders -->
    <section class="section-private-folders section-full">
        <div class="col-description">
            <h2><?php esc_html_e( 'User Private Files', 'integrate-google-drive' ); ?></h2>

            <p><?php esc_html_e( 'Using Private Files you can easily and securely share your Google Drive documents with your
                users/clients. This allows your users/ clients to view, download and manage their documents in their own
                private folders.', 'integrate-google-drive' ); ?></p>

            <p><?php esc_html_e( 'You can also link your users to the specific folders to your Google Drive cloud files.', 'integrate-google-drive' ); ?></p>

            <h4><?php esc_html_e( 'The Private Folders can be useful in some situations, for example:', 'integrate-google-drive' ); ?></h4>
            <ol>
                <li><?php esc_html_e( 'You want your clients, users or guests upload files to their own folder', 'integrate-google-drive' ); ?></li>
                <li><?php esc_html_e( 'You want to share documents with your users/ clients privately', 'integrate-google-drive' ); ?></li>
                <li><?php esc_html_e( 'Private Folders can be assigned to a user both automatically or manually.', 'integrate-google-drive' ); ?></li>
                <li><?php esc_html_e( 'You can assign the folders to the users manually from the Private Folders page.', 'integrate-google-drive' ); ?></li>
            </ol>

            <h4><?php esc_html_e( 'To link the folders for the users, follow the below steps:', 'integrate-google-drive' ); ?></h4>
            <ol>
                <li><?php esc_html_e( 'Go to Admin Dashboard > Google Drive > Private Folders', 'integrate-google-drive' ); ?></li>
                <li><?php esc_html_e( 'Click on the Select Folders button on the users list. Then a new popup dialog will open.', 'integrate-google-drive' ); ?></li>
                <li><?php esc_html_e( 'Select the folders from the popup dialog.', 'integrate-google-drive' ); ?></li>
                <li><?php esc_html_e( 'Then click on the Done button on the top of the dialog.', 'integrate-google-drive' ); ?></li>
                <li><?php esc_html_e( 'You can also enable the auto creates private folders for each user when a new user is registered
                    from
                    the Settings page.', 'integrate-google-drive' ); ?>
                </li>
            </ol>

            <h4><?php esc_html_e( 'For displaying the private folders for the users, follow the below steps:', 'integrate-google-drive' ); ?></h4>
            <ol>
                <li><?php esc_html_e( 'Go to the Shortcode Builder and create a new shortcode module.', 'integrate-google-drive' ); ?></li>
                <li><?php esc_html_e( 'On the source tab, turn ON the Use Private Folders option.', 'integrate-google-drive' ); ?></li>
                <li><?php esc_html_e( 'Copy the shortcode and use it any page.', 'integrate-google-drive' ); ?></li>
                <li><?php esc_html_e( 'Now, while the users will visit the page they can navigate through the folders that are linked to
                    their
                    account.', 'integrate-google-drive' ); ?>
                </li>
            </ol>
        </div>

        <div class="col-image">
            <iframe
                    src="https://www.youtube.com/embed/nEGVLPESJl4?rel=0"
                    title="YouTube video player" frameBorder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowFullScreen></iframe>
        </div>

    </section>

</div>