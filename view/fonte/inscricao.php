<?php
// Automação de Criação de Sistema - 13/01/2020 07:34:45
if (!defined("SISTEMA_LIBRARY")) {
    die("Acesso direto não permitido");
}

include Config::getData('path') . '/view/template/template1.php';
?>


<div class="text-center">
    <iframe name="iframe_form" width="100%" height="760px" frameborder="0" style="background: none; border: none;" allowtransparency="true" src="https://logos.usenextstep.com.br/public/form-inscricao/10/MmQyVzR0SVZLeFBuZXBKS01Idmk1eUtheGFnQk9CNjJIMTRUazZYYlMvYVY0QzM1Y0FOTFMxTTVBOStQUHc9PQ==">
        Seu navegador não possui suporte para iframes.
    </iframe>
</div>

<script>
    $(document).ready(function () {
        setTimeout(function () {
            $(".page-title").removeClass('d-none d-lg-block').hide();
        }, 500);

    });
</script>
<?php
include Config::getData('path') . '/view/template/template2.php';
