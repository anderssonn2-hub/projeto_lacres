## Checklist de deploy do malote

### 1. Arquivo que elimina a pagina em branco

Publicar a versao atual de `lacres_novo.php`.

Motivo:
- o fatal de PHP 5.3 no servidor aponta para `Can't use function return value in write context` na linha 2760
- esse trecho ja foi corrigido no repositorio usando variavel temporaria antes do teste com `empty`/`trim`

Trecho corrigido no repositorio:
- `lacres_novo.php` em torno da linha 2758

### 2. Arquivos PHP que precisam existir em `/controle/malote/`

- `inicio.php`
- `lacres_novo.php`
- `conferencia_pacotes.php`
- `conferencia_pacotes_previa.php`
- `encontra_posto.php`
- `melhorias_widget.php`
- `melhorias_widget_api.php`
- `processando_overlay.php`

### 3. Assets requeridos pelo fluxo

Arquivos referenciados diretamente pelas paginas:

- `beep.mp3`
- `pertence_aos_correios.mp3`
- `logo_celepar.png`
- `concluido.mp3`
- `pacotejaconferido.mp3`
- `pacotedeoutraregional.mp3`
- `posto_poupatempo.mp3`

Observacoes:
- `beep.mp3` nao existe hoje na raiz do projeto; ha uma copia em `.devcontainer/beep.mp3`
- `pertence_aos_correios.mp3` nao foi localizado neste repositorio
- `logo_celepar.png` nao foi localizado neste repositorio

### 4. Sintomas esperados se o deploy estiver incompleto

- pagina em branco ao abrir `lacres_novo.php`: servidor ainda executando a versao antiga
- erro de widget: falta `melhorias_widget_api.php`
- botoes/sons sem funcionar: falta de `beep.mp3` ou `pertence_aos_correios.mp3`
- logotipo quebrado na previa/oficio: falta `logo_celepar.png`

### 5. Validacao minima no servidor

Depois do upload, validar:

1. abrir `inicio.php`
2. abrir `lacres_novo.php`
3. abrir `conferencia_pacotes.php`
4. abrir `encontra_posto.php`
5. confirmar que o `erro.log` nao registra mais o fatal na linha 2760
6. confirmar que `melhorias_widget_api.php` deixa de aparecer como `script not found`