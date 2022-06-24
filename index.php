<?php
/**
 * Hybula Looking Glass
 *
 * Provides UI and input for the looking glass backend.
 *
 * @copyright 2022 Hybula B.V.
 * @license Mozilla Public License 2.0
 * @version 0.1
 * @since File available since release 0.1
 * @link https://github.com/hybula/lookingglass
 */

declare(strict_types=1);

require __DIR__.'/config.php';
require __DIR__.'/LookingGlass.php';

use Hybula\LookingGlass;

LookingGlass::validateConfig();
LookingGlass::startSession();
$detectIpAddress = LookingGlass::detectIpAddress();

$links = [
    'Claro NET Virtua' => '0',
    'Veloo Telecom' => '1',
    'Brisanet' => '2'
];
if (!isset($_SESSION['LINK']))
    $_SESSION['LINK'] = '0';

if (!empty($_POST)) {
    do {
        if (!isset($_POST['csrfToken']) || !isset($_SESSION['CSRF']) || ($_POST['csrfToken'] != $_SESSION['CSRF'])) {
            $errorMessage = 'CSRF inválido.';
            break;
        }
        if (isset($_POST['submitForm'])) {
            if (!in_array($_POST['backendMethod'], LG_METHODS)) {
                $errorMessage = 'Backend não suportado';
                break;
            }
            if (!in_array($_POST['link'], ['0', '1', '2'])) {
                $errorMessage = 'Backhaul não suportado';
                break;
            }
            $_SESSION['METHOD'] = $_POST['backendMethod'];
            $_SESSION['TARGET'] = $_POST['targetHost'];
            $_SESSION['LINK'] = $_POST['link'];

            if (in_array($_POST['backendMethod'], ['ping', 'mtr', 'traceroute'])) {
                if (!LookingGlass::isValidIpv4($_POST['targetHost'])) {
                    $targetHost = LookingGlass::isValidHost($_POST['targetHost'], 'ipv4');
                    if (!$targetHost) {
                        $errorMessage = 'IPv4 inválido.';
                        break;
                    }
                    $_SESSION['TARGET'] = $targetHost;
                }
            }
            $_SESSION['TERMS'] = true;
            $_SESSION['BACKEND'] = true;
            break;
        }
        $errorMessage = 'Requisição inválida.';
        break;
    } while (true);
}

$_SESSION['CSRF'] = bin2hex(random_bytes(12));

if (LG_BLOCK_CUSTOM) {
    include LG_CUSTOM_PHP;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1" name="viewport">
    <meta content="" name="description">
    <meta content="Hybula" name="author">
    <title><?php echo LG_TITLE; ?></title>
    <link crossorigin="anonymous" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" rel="stylesheet">
    <?php if (LG_CSS_OVERRIDES) { echo '<link href="'.LG_CSS_OVERRIDES.'" rel="stylesheet">'; } ?>
</head>
<body>

<div class="col-lg-6 mx-auto p-3 py-md-5">

    <form method="POST" action="/" autocomplete="off">
    <header class="row d-flex align-items-center pb-3 mb-5 border-bottom">
            <div class="col-12 col-md-8">
                <p class="d-flex align-items-center text-dark text-decoration-none" >
                    <?php echo LG_LOGO; ?>
                </p>
            </div>
            <div class="col-12 col-md-4 float-end">
                <label>Selecione um provedor</label>
                <select id="link" name="link" class="form-select">
                    <?php foreach ($links as $name => $value) { ?>
                        <option value="<?php echo $value; ?>"<?php if (isset($_SESSION['LINK']) && $_SESSION['LINK'] == $value) { echo 'selected'; } ?>><?php echo $name; ?></option>
                    <?php } ?>
                </select>
            </div>
    </header>

    <main>

        <?php if (LG_BLOCK_NETWORK) { ?>
        <div class="row mb-5">
            <div class="card shadow-lg">
                <div class="card-body p-3">
                    <h1 class="fs-4 card-title mb-4">Informações</h1>

                    <div class="row mb-3">
                        <div class="col-md-7">
                            <label class="mb-2 text-muted">Localização do servidor</label>
                            <div class="input-group mb-3">
                                <p><?php echo LG_LOCATION; ?></p>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <label class="mb-2 text-muted">Seu IP</label>
                            <div class="input-group">
                                <p><?php echo $detectIpAddress; ?></p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <?php } ?>

        <?php if (LG_BLOCK_LOOKINGGLAS) { ?>
        <div class="row pb-5">
            <div class="card shadow-lg">
                <div class="card-body p-3">
                    <h1 class="fs-4 card-title mb-4">Looking Glass</h1>
                        <input type="hidden" name="csrfToken" value="<?php echo $_SESSION['CSRF']; ?>">

                        <div class="row">
                            <div class="col-md-7 mb-3">
                                <div class="input-group">
                                    <span class="input-group-text" id="basic-addon1">Destino</span>
                                    <input type="text" class="form-control" placeholder="IP address or host..." name="targetHost" value="<?php if (isset($_SESSION['TARGET'])) { echo $_SESSION['TARGET']; } ?>" required="">
                                </div>
                            </div>
                            <div class="col-md-5 mb-3">
                                <div class="input-group">
                                    <label class="input-group-text">Método</label>
                                    <select class="form-select" name="backendMethod" id="backendMethod">
                                        <?php foreach (LG_METHODS as $method) { ?>
                                            <option value="<?php echo $method; ?>"<?php if (isset($_SESSION['METHOD']) && $_SESSION['METHOD'] == $method) { echo 'selected'; } ?>><?php echo $method; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center">

                            <button type="submit" class="btn btn-primary ms-auto" id="executeButton" name="submitForm">
                                Executar
                            </button>
                        </div>

                        <?php if (isset($errorMessage)) echo '<div class="alert alert-danger mt-3" role="alert">'.$errorMessage.'</div>'; ?>

                        <div class="card card-body bg-light mt-4" style="display: none;" id="outputCard">
                            <pre id="outputContent" style="overflow: hidden; white-space: pre; word-wrap: normal;"></pre>
                        </div>

                </div>
            </div>
        </div>
        <?php } ?>


    </main>
    <footer class="pt-3 mt-5 my-5 text-muted border-top">
        Powered by <a href="https://github.com/amorim/lookingglass" target="_blank">Looking Glass</a>
    </footer>

    </form>
</div>

<script type="text/javascript">
    <?php if (isset($_SESSION['BACKEND'])) { echo 'callBackend();'; } ?>
    function callBackend() {
        const executeButton = document.getElementById('executeButton');
        executeButton.innerText = 'Executing...';
        executeButton.disabled = true;
        document.getElementById('outputCard').style.display = 'inherit';
        const xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
            document.getElementById('outputContent').innerHTML = this.responseText.replace(/<br \/> +/g, '<br />');
            if (this.readyState === XMLHttpRequest.DONE) {
                executeButton.innerText = 'Execute';
                executeButton.disabled = false;
                console.log('Backend ready!');
            }
        };
        xhr.open('GET', 'backend.php', true);
        xhr.send();
    }
</script>

<script type="text/javascript">
    async function copyToClipboard(text, button) {
        button.innerHTML = 'Copied!';
        const textAreaObject = document.createElement('textarea');
        textAreaObject.value = text;
        document.body.appendChild(textAreaObject);
        textAreaObject.select();
        document.execCommand('copy');
        document.body.removeChild(textAreaObject);
        await new Promise(r => setTimeout(r, 2000));
        button.innerHTML = 'Copy';
    }
</script>

<script crossorigin="anonymous" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
