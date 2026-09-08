  <!-- ========== メインコンテンツ ========== -->
  <main class="mem-main">
    <div class="mem-card-wrap">
      <div class="mem-card">

        <p class="mem-breadcrumb mem-breadcrumb--pc"><a href="/">関西不動産情報センターTOP</a> &gt; 会員専用ページ ログイン</p>

        <?php if (!empty($tpl_errDoc)): ?>
        <p class="mem-login-msg"><?php foreach ($tpl_errDoc as $val) { echo h($val); } ?></p>
        <hr class="mem-login-divider">
        <?php endif; ?>

        <form class="mem-login-form" action="<?php echo HTTP_TO_SCRIPT_NAME; ?>" method="post">
          <input type="hidden" name="cmd" value="login">
          <input type="hidden" name="session" value="<?php echo isset($tpl_disp['session']) ? h($tpl_disp['session']) : ''; ?>">

          <div class="reg-table">

            <div class="reg-row">
              <label class="reg-label" for="login_id">ログインID</label>
              <div class="reg-input">
                <input type="text" id="login_id" name="id" value="<?php echo isset($tpl_disp['id']) ? h($tpl_disp['id']) : ''; ?>">
              </div>
            </div>

            <div class="reg-row">
              <label class="reg-label" for="password">パスワード</label>
              <div class="reg-input">
                <input type="password" id="password" name="pass">
                <p class="mem-login-pw-hint">※大文字・小文字の英字および数字を組み合わせた8文字以上で設定してください。</p>
              </div>
            </div>

          </div>

          <p class="mem-login-pw-link"><a href="/<?php echo APP_NAME; ?>/reminder">※パスワードの変更・再発行はこちら →</a></p>

          <div class="mem-login-btn-row">
            <button type="submit" class="mem-login-btn">
              <span class="btn-text">ログイン</span>
              <span class="btn-sep">|</span>
              <span class="btn-arrow">&#x25B6;&#xFE0E;</span>
            </button>
          </div>

        </form>

      </div>
    </div>
  </main>
