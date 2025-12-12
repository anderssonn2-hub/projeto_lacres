# Como funciona a geração do nome do arquivo PDF

## ⚠️ IMPORTANTE: Nome do arquivo PDF

O nome do arquivo PDF quando você clica em "Imprimir" e salva como PDF **é controlado pelo navegador**, não pelo código PHP.

### Como o navegador define o nome do arquivo:

1. **O navegador usa a tag `<title>` do HTML** como sugestão para o nome do arquivo
2. O código em `modelo_oficio_poupa_tempo.php` define:
   ```php
   $titulo_pdf = $id_despacho_post . '_poupatempo_' . $data_titulo;
   ```
   Exemplo: `97_poupatempo_11-12-2025`

3. **O navegador pode adicionar prefixo ou sufixo automaticamente:**
   - Chrome: Pode adicionar "#" no início (comportamento do navegador)
   - Firefox: Usa o título direto
   - Edge: Pode adicionar caracteres especiais

### ✅ Solução implementada na v8.15.6:

O código PHP **já está correto** e NÃO adiciona `#` no título:

```php
// modelo_oficio_poupa_tempo.php linha ~503
$titulo_pdf = $id_despacho_post . '_poupatempo_' . $data_titulo;
// Resultado: "97_poupatempo_11-12-2025" (SEM #)
```

### 🔧 Se o arquivo ainda é salvo com `#`:

1. **Limpe o cache do navegador** (Ctrl+Shift+Del)
2. **Tente outro navegador** (Firefox em vez de Chrome)
3. **Use "Salvar como" no diálogo de impressão** e remova o `#` manualmente
4. **Configure o Chrome:**
   - Vá em `chrome://settings/`
   - Procure por "Downloads"
   - Desative "Perguntar onde salvar cada arquivo antes de fazer o download"

### 📁 Estrutura de arquivos atual:

```
Q:\cosep\IIPR\Oficios\
  └── 2025\
      └── Dezembro\
          ├── correios\
          │   └── 97_correios_11-12-2025.pdf
          └── poupatempo\
              └── 90_poupatempo_11-12-2025.pdf
```

**Formato correto:** `{ID}_{tipo}_{dd-mm-yyyy}.pdf` (SEM #)

---

## 🔗 Links clicáveis

Os links na coluna "Link" da consulta agora são **totalmente clicáveis**:

- **Formato visual:** `#97` (mostra o ID com #)
- **URL:** `file:///Q:cosep/IIPR/Oficios/2025/Dezembro/correios/97_correios_11-12-2025.pdf`
- **Ação:** Abre em nova aba ao clicar
- **Hover:** Mostra caminho completo Windows

---

## 🆕 Modo "Criar Novo" vs "Sobrescrever"

### v8.15.6 - CORRIGIDO ✅

- **Criar Novo:** Sempre cria um novo ofício com **novo ID** (hash único com microtime)
- **Sobrescrever:** Substitui o ofício existente (mesmo ID)

### Como funciona:

```php
// Hash único para "Criar Novo"
$hash = sha1($grupo . '|' . $datasStr_post . '|' . time() . '|' . microtime(true));

// Hash fixo para "Sobrescrever"
$hash = sha1($grupo . '|' . $datasStr_post);
```

---

**Versão atual:** 8.15.6 (11/12/2025)
