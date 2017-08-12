<div class="wrap" id="gravity-merge-wrap">
    <h1>Gravity 2 PDF - Google Drive</h1>
    <br />
    <?php settings_errors() ?>
    <div class="content-wrap">
        <?php
            $gmergegoogle_settings_options = get_option('gmergegoogle_settings_options');
            $client_id            = isset($gmergegoogle_settings_options['client_id']) ? $gmergegoogle_settings_options['client_id'] : '';
            $client_secret        = isset($gmergegoogle_settings_options['client_secret']) ? $gmergegoogle_settings_options['client_secret'] : '';
            $token                = isset($gmergegoogle_settings_options['token']) ? $gmergegoogle_settings_options['token'] : '';
        ?>
        <br />
        <form method="post" action="options.php">
            <?php settings_fields( 'gmergegoogle_settings_options' ); ?>
            <?php do_settings_sections( 'gmergegoogle_settings_options' ); ?> 
            <table class="form-table">
                <tbody>
                    <tr class="form-field form-required term-name-wrap">
                        <th scope="row">
                            <label>Client ID</label>
                        </th>
                        <td>
                            <input type="text" name="gmergegoogle_settings_options[client_id]" size="40" width="40" value="<?= $client_id ?>">
                        </td>
                    </tr>
                    <tr class="form-field form-required term-name-wrap">
                        <th scope="row">
                            <label>Client Secret</label>
                        </th>
                        <td>
                            <input type="text" name="gmergegoogle_settings_options[client_secret]" size="40" width="40" value="<?= $client_secret ?>">
                        </td>
                    </tr>
                    <tr class="form-field form-required term-name-wrap">
                        <th scope="row">
                            <label>Google Token</label>
                        </th>
                        <td>
                            <textarea rows="5" readonly="" name="gmergegoogle_settings_options[token]"><?= $token ?></textarea>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p>
                <input type="submit" name="save_settings" class="button button-primary" value="Save">
                <?php if (!empty($client_id) && !empty($client_secret)): ?>
                <a href="<?= admin_url( 'admin.php?page=gravitymergegoogle&integration=googledrive' ); ?>" class="button button-primary">Get Access Token</a>
                <?php endif; ?>
            </p>
        </form>
    </div>
</div>