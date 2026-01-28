# Correções Necessárias para v9.20.0

## ⚠️ IMPORTANTE
O código que você forneceu é diferente do arquivo atual no repositório.
Você precisa **SUBSTITUIR** o arquivo `modelo_oficio_poupa_tempo.php` pelo código que você enviou, e então aplicar estas correções:

---

## 1. Atualizar Changelog (linha ~9)

**SUBSTITUIR:**
```php
   v9.19.0: CORREÇÃO DEFINITIVA - Layout Vertical (28/01/2026)
```

**POR:**
```php
   v9.20.0: Correções de Clonagem e Layout (28/01/2026)
   - [CORRIGIDO] Recálculo de total em páginas clonadas agora funciona corretamente
   - [CORRIGIDO] Botão "REMOVER" agora aparece dentro da própria página clonada
   - [CORRIGIDO] Botão "REMOVER" oculto na impressão (.nao-imprimir)
   - [NOVO] Cabeçalho COSEP com logo (substituiu "Governo de São Paulo")
   - [NOVO] Código do posto visível junto com nome (formato: "001 - Nome do Posto")
   - [MELHORADO] Função recalcularTotal() atualiza displays corretos em clones
   
   v9.19.0: CORREÇÃO DEFINITIVA - Layout Vertical (28/01/2026)
```

---

## 2. Corrigir CSS do Botão Remover (dentro do <style>)

**LOCALIZAR:**
```css
/* v9.14.0: Botão de remover página clonada */
.btn-remover-pagina{
    position:absolute;
    top:10px;
    right:10px;
    ...
}
```

**SUBSTITUIR POR:**
```css
/* v9.20.0: Botão de remover página clonada - DENTRO da página */
.btn-remover-pagina{
    display:inline-block;
    margin:10px auto 20px auto;
    padding:8px 16px;
    background:#dc3545;
    color:#fff;
    border:2px solid #bd2130;
    border-radius:6px;
    font-size:13px;
    font-weight:bold;
    cursor:pointer;
    text-align:center;
    box-shadow:0 2px 5px rgba(220,53,69,0.3);
    transition:all 0.2s;
    width:100%;
    max-width:300px;
}
.btn-remover-pagina:hover{
    background:#c82333;
    border-color:#a71d2a;
    transform:translateY(-2px);
    box-shadow:0 4px 8px rgba(220,53,69,0.4);
}
```

---

## 3. Corrigir Função recalcularTotal() (dentro do <script>)

**LOCALIZAR:**
```javascript
// v9.8.2: Recalcular total de quantidade baseado nos lotes marcados
function recalcularTotal(containerId) {
    var container = document.getElementById(containerId);
    if (!container) return;
```

**SUBSTITUIR TODA A FUNÇÃO POR:**
```javascript
// v9.20.0: Recalcular total de quantidade - CORRIGIDO para clones
function recalcularTotal(containerId) {
    var container = document.getElementById(containerId);
    if (!container) {
        // Tenta buscar por data-posto se não encontrou por ID
        container = document.querySelector('[data-posto="' + containerId + '"]');
    }
    if (!container) return;
    
    var checkboxes = container.querySelectorAll('.checkbox-lote:checked');
    var total = 0;
    
    checkboxes.forEach(function(cb) {
        var quantidade = parseInt(cb.getAttribute('data-quantidade'), 10);
        if (!isNaN(quantidade)) {
            total += quantidade;
        }
    });
    
    // v9.20.0: Atualiza TODOS os displays de total neste container
    var inputTotal = container.querySelector('.input-total-quantidade');
    if (inputTotal) {
        inputTotal.value = total;
    }
    
    var displayTotal = container.querySelector('.display-total-lotes');
    if (displayTotal) {
        displayTotal.textContent = total.toLocaleString('pt-BR');
    }
    
    var totalCins = container.querySelector('.total-cins-display');
    if (totalCins) {
        totalCins.textContent = total.toLocaleString('pt-BR');
    }
    
    // Atualiza data-checked nas linhas
    var linhas = container.querySelectorAll('.linha-lote');
    linhas.forEach(function(linha) {
        var cb = linha.querySelector('.checkbox-lote');
        if (cb) {
            linha.setAttribute('data-checked', cb.checked ? '1' : '0');
        }
    });
}
```

---

## 4. Corrigir Função clonarPagina() (dentro do <script>)

**LOCALIZAR:**
```javascript
// v9.14.0: Clonar página para dividir malotes
function clonarPagina(postoId) {
```

**SUBSTITUIR TODA A FUNÇÃO POR:**
```javascript
// v9.20.0: Clonar página - CORRIGIDO botão remover dentro da página
function clonarPagina(postoId) {
    var paginaOriginal = document.querySelector('.folha-a4-oficio[data-posto="' + postoId + '"]');
    if (!paginaOriginal) {
        alert('Pagina nao encontrada para posto: ' + postoId);
        return;
    }
    
    // Clona a página
    var paginaClone = paginaOriginal.cloneNode(true);
    
    // Gera novo ID único para a página clonada
    var timestamp = Date.now();
    var novoId = postoId + '_clone_' + timestamp;
    paginaClone.setAttribute('data-posto', novoId);
    paginaClone.setAttribute('id', 'pagina_' + novoId);
    
    // Remove botão de remover se já existir (evita duplicação)
    var btnRemoverAntigo = paginaClone.querySelector('.btn-remover-pagina');
    if (btnRemoverAntigo) {
        btnRemoverAntigo.remove();
    }
    
    // v9.20.0: Adiciona botão de remover DENTRO do div.oficio (primeira posição)
    var divOficio = paginaClone.querySelector('.oficio');
    if (divOficio) {
        var containerBtnRemover = document.createElement('div');
        containerBtnRemover.className = 'nao-imprimir';
        containerBtnRemover.style.cssText = 'text-align:center;margin-bottom:15px;padding:10px;background:#fff3cd;border:2px dashed #ffc107;border-radius:6px;';
        
        var btnRemover = document.createElement('button');
        btnRemover.type = 'button';
        btnRemover.className = 'btn-remover-pagina';
        btnRemover.innerHTML = '✕ REMOVER ESTA PÁGINA CLONADA';
        btnRemover.onclick = function() {
            if (confirm('Deseja remover esta pagina clonada?')) {
                paginaClone.remove();
            }
        };
        
        containerBtnRemover.appendChild(btnRemover);
        // Insere como primeiro elemento do oficio
        divOficio.insertBefore(containerBtnRemover, divOficio.firstChild);
    }
    
    // Atualiza IDs dos elementos internos para evitar conflitos
    var elementosComId = paginaClone.querySelectorAll('[id]');
    elementosComId.forEach(function(el) {
        if (el.id && el.id !== 'pagina_' + novoId) {
            el.id = el.id.replace(/clone_\d+$/, '') + '_clone_' + timestamp;
        }
    });
    
    // v9.20.0: Reativa os eventos de checkbox com closure correto
    var checkboxes = paginaClone.querySelectorAll('.checkbox-lote');
    checkboxes.forEach(function(cb) {
        // Remove event listeners antigos
        var novoCb = cb.cloneNode(true);
        cb.parentNode.replaceChild(novoCb, cb);
        
        // Adiciona novo event listener
        novoCb.addEventListener('change', function() {
            recalcularTotal(novoId);
        });
    });
    
    // Limpa o lacre da página clonada
    var inputLacre = paginaClone.querySelector('input[name^="lacre_iipr"]');
    if (inputLacre) {
        inputLacre.value = '';
        inputLacre.placeholder = 'Digite novo lacre para esta página';
    }
    
    // Insere a página clonada após a original
    paginaOriginal.parentNode.insertBefore(paginaClone, paginaOriginal.nextSibling);
    
    // Recalcula o total da página clonada
    setTimeout(function() {
        recalcularTotal(novoId);
    }, 100);
    
    // Scroll para a nova página
    paginaClone.scrollIntoView({ behavior: 'smooth', block: 'start' });
    
    alert('✓ Pagina clonada com sucesso!\n\n' +
          '• Marque/desmarque os lotes conforme necessario\n' +
          '• Informe um novo numero de lacre\n' +
          '• O total sera recalculado automaticamente');
}
```

---

## 5. Substituir Cabeçalho no HTML (dentro do foreach)

**LOCALIZAR:**
```php
    <div class="folha-a4-oficio" id="<?php echo e($pageId); ?>" data-posto="<?php echo e($codigo); ?>">
        <div class="oficio">
            <!-- Cabeçalho -->
            <div class="cols100 center">
                <h3>GOVERNO DO ESTADO DE SAO PAULO</h3>
                <h4>SECRETARIA DA SEGURANCA PUBLICA</h4>
                <h4>INSTITUTO DE IDENTIFICACAO RICARDO GUMBLETON DAUNT</h4>
            </div>

            <div class="cols100 center border-1px p5">
```

**SUBSTITUIR POR:**
```php
    <div class="folha-a4-oficio" id="<?php echo e($pageId); ?>" data-posto="<?php echo e($codigo); ?>">
        <div class="oficio">
            <!-- v9.20.0: Cabeçalho COSEP com logo -->
            <div class="cols100 border-1px">
                <div class="cols25 fleft margin2px">
                    <img alt="Logotipo" style="margin-left:10px;margin-top:10px;padding-right:15px;float:left" src="logo_celepar.png" width="250" height="55">
                </div>
                <div class="cols65 fright center margin2px">
                    <h3><i>COSEP <br> Coordenacao De Servicos De Producao</i></h3>
                    <h3><b><br> Comprovante de Entrega </b></h3>
                </div>
            </div>

            <!-- v9.20.0: Nome do posto COM código visível -->
            <div class="cols100 center border-1px p5 moldura">
                <h4 class="left">
                    <br><span class="nometit">POUPATEMPO PARANA - Posto <?php echo e($codigo); ?></span>
                    <br><span class="nometit">ENDERECO: 
                        <input type="text" 
                               name="endereco_posto[<?php echo e($codigo); ?>]" 
                               value="<?php echo e($enderecoExibir); ?>" 
                               class="input-editavel"
                               style="width:90%;">
                    </span>
                    <br><span class="nometit"></span>
                </h4>
            </div>

            <!-- Nome editável do posto -->
            <div class="cols100 center border-1px p5">
```

---

## 6. Alterar Display do Total de CINs

**LOCALIZAR:**
```php
            <!-- Total de CIN's -->
            <div class="cols100 border-1px p5">
                <strong>Total de CIN's:</strong>
                <input type="text" name="quantidade_posto[<?php echo e($codigo); ?>]" 
                       value="<?php echo (int)$quantidadeExibir; ?>" 
                       class="input-editavel input-total-quantidade"
                       style="width:100px;text-align:center;">
            </div>
```

**SUBSTITUIR POR:**
```php
            <!-- v9.20.0: Total de CIN's com display visual -->
            <div class="cols100 border-1px p5">
                <strong>Total de CIN's:</strong>
                <span class="total-cins-display" style="font-weight:bold;color:#28a745;margin-left:10px;font-size:18px;"><?php echo number_format($quantidadeExibir, 0, ',', '.'); ?></span>
                <input type="hidden" name="quantidade_posto[<?php echo e($codigo); ?>]" 
                       value="<?php echo (int)$quantidadeExibir; ?>" 
                       class="input-total-quantidade">
            </div>
```

---

## ✅ Checklist de Aplicação

Após fazer TODAS as alterações acima:

- [ ] Changelog atualizado para v9.20.0
- [ ] CSS do botão remover atualizado
- [ ] Função recalcularTotal() corrigida
- [ ] Função clonarPagina() corrigida
- [ ] Cabeçalho COSEP implementado
- [ ] Código do posto visível
- [ ] Total de CINs como display (não input)
- [ ] Testar no navegador
- [ ] Verificar clonagem funciona
- [ ] Verificar total recalcula ao desmarcar
- [ ] Verificar botão remover aparece na página clonada
- [ ] Verificar impressão oculta botão remover

---

## 🎯 Resultado Esperado

✓ Layout mantido (páginas uma abaixo da outra)  
✓ Cabeçalho COSEP com logo  
✓ Código do posto visível ("Posto 001 - Nome")  
✓ Clonagem funciona perfeitamente  
✓ Total recalcula em páginas clonadas  
✓ Botão remover dentro da página (não no topo)  
✓ Botão remover oculto na impressão  
✓ Total de CINs atualiza automaticamente
