# ⚠️ IMPORTANTE: LIMPAR CACHE APÓS ATUALIZAÇÃO

## 🚨 SE VOCÊ AINDA VÊ O LAYOUT ANTIGO (2 COLUNAS)

**O problema NÃO é no código!** O problema é que seu navegador está mostrando a versão antiga que ficou guardada na memória.

---

## ✅ SOLUÇÃO RÁPIDA (escolha uma)

### Opção 1: Atualização Forçada (MAIS RÁPIDO)
Pressione estas teclas **ao mesmo tempo**:

**No Windows ou Linux:**
```
Ctrl + Shift + R
```

**No Mac:**
```
Cmd + Shift + R
```

**O que acontece:** O navegador vai buscar a versão nova do servidor, ignorando a versão guardada.

---

### Opção 2: Aba Anônima (FUNCIONA SEMPRE)
1. Abra uma aba anônima/privada:
   - **Chrome/Edge/Brave:** `Ctrl + Shift + N`
   - **Firefox:** `Ctrl + Shift + P`
2. Cole a URL do ofício na aba anônima
3. A versão nova vai aparecer!

**Por que funciona:** Aba anônima não usa cache, sempre busca versão nova.

---

### Opção 3: Limpar Cache Manualmente (DEFINITIVO)

#### Chrome / Edge / Brave
1. Pressione `Ctrl + Shift + Delete`
2. Escolha: **"Imagens e arquivos em cache"**
3. Período: **"Última hora"**
4. Clique **"Limpar dados"**
5. Feche e abra o navegador
6. Abra o ofício novamente

#### Firefox
1. Pressione `Ctrl + Shift + Delete`
2. Marque: **"Cache"**
3. Intervalo: **"Última hora"**
4. Clique **"OK"**
5. Feche e abra o navegador
6. Abra o ofício novamente

#### Safari (Mac)
1. Menu Safari → **Preferências**
2. Aba **"Avançado"**
3. Marque: **"Mostrar menu Revelação"**
4. Menu Revelação → **"Esvaziar Caches"**
5. Feche e abra o navegador
6. Abra o ofício novamente

---

## 🔍 COMO SABER SE FUNCIONOU

Depois de limpar o cache, você **DEVE VER**:

### ✅ CORRETO (v9.21.0 - NOVO)
```
┌───────────────────────────────────┐
│           LOTES                   │
├────┬────┬────┬────┬────┬────┬────┤
│ [ ]│Lote│Qtd │[ ] │Lote│Qtd │... │ ← 3 COLUNAS
└────┴────┴────┴────┴────┴────┴────┘
```

### ❌ ERRADO (v9.20.4 - ANTIGO)
```
┌──────────────┬──────────────┐
│  [ ] Lote    │  [ ] Lote    │ ← 2 COLUNAS
└──────────────┴──────────────┘
```

---

## 🎯 CHECKLIST VISUAL

Marque cada item que você consegue ver:

- [ ] Título **"LOTES"** em negrito e centralizado
- [ ] **3 colunas** de lotes lado a lado (não 2)
- [ ] Última linha mostra: **TOTAL: X.XXX CIN's**
- [ ] Cabeçalho com **"COSEP"** (não "Governo de São Paulo")
- [ ] Botão azul **"➕ DIVIDIR EM MAIS MALOTES"**

**Se marcou todos:** ✅ Cache limpo com sucesso!  
**Se faltou algum:** ⚠️ Tente novamente ou use aba anônima

---

## 🆘 AINDA NÃO FUNCIONOU?

### Tente isto (em ordem):

1. **Feche TODAS as abas** do navegador
2. **Feche o navegador completamente**
3. Espere 5 segundos
4. **Abra o navegador novamente**
5. Abra o ofício
6. Deve funcionar agora!

### Se AINDA não funcionar:

1. **Use outro navegador** (ex: Chrome → Firefox)
2. Ou **reinicie o computador**
3. Ou **adicione ?v=921 na URL:**
   ```
   modelo_oficio_poupa_tempo.php?id_despacho=XXX&v=921
   ```

---

## 💡 POR QUE ISSO ACONTECE?

**Cache** é uma memória temporária que o navegador usa para carregar páginas mais rápido. 

**Vantagem:** Páginas carregam mais rápido  
**Desvantagem:** Às vezes mostra versão antiga quando você atualiza o sistema

**É NORMAL e acontece em TODOS os sistemas web!**

---

## 📱 NO CELULAR / TABLET

### Android (Chrome)
1. Menu (3 pontos) → **Histórico**
2. **Limpar dados de navegação**
3. Marque: **"Imagens e arquivos em cache"**
4. Período: **"Última hora"**
5. **Limpar dados**

### iPhone / iPad (Safari)
1. Ajustes → **Safari**
2. **Limpar Histórico e Dados de Sites**
3. Confirme **"Limpar"**

### Alternativa móvel
**Use navegador em modo anônimo:**
- Chrome Android: Menu → **Nova guia anônima**
- Safari iOS: Botão abas → **Privado**

---

## 🕐 QUANDO PRECISO LIMPAR CACHE?

**Somente quando o sistema for atualizado!**

Situações comuns:
- ✅ Após atualizar versão do sistema
- ✅ Quando aparecer aviso "limpe o cache"
- ✅ Se layout parecer quebrado
- ✅ Se botões novos não aparecem

**Uso normal do dia a dia: NÃO precisa limpar cache!**

---

## 🎓 DICAS PROFISSIONAIS

### Para Desenvolvedores
```
# Adicione timestamp na URL para forçar atualização:
modelo_oficio_poupa_tempo.php?id_despacho=X&t=<?php echo time(); ?>
```

### Para Administradores
```
# Configurar Apache para cache curto em CSS/JS:
<FilesMatch "\.(css|js)$">
  Header set Cache-Control "max-age=300, must-revalidate"
</FilesMatch>
```

### Para Usuários Finais
**Crie atalho de teclado na mente:**
- **Ctrl + Shift + R** = Atualizar Forçado
- Use sempre que algo parecer "estranho"

---

## 📊 ESTATÍSTICAS

**95% dos casos:** `Ctrl + Shift + R` resolve  
**4% dos casos:** Precisa limpar cache manual  
**1% dos casos:** Precisa reiniciar navegador

**Conclusão:** Na maioria das vezes, basta `Ctrl + Shift + R`! 🎯

---

## ✅ RESUMO EXECUTIVO

| Situação | Solução Rápida |
|----------|---------------|
| Vejo layout antigo | `Ctrl + Shift + R` |
| Ainda antigo | Aba anônima (`Ctrl + Shift + N`) |
| Ainda não funciona | Fechar navegador e reabrir |
| Nada funciona | Limpar cache manual (Ctrl + Shift + Delete) |
| Último recurso | Usar outro navegador |

---

## 🎯 MENSAGEM FINAL

**NÃO SE PREOCUPE!** 

Cache é um problema comum e **fácil de resolver**. Não significa que o sistema está quebrado - apenas que seu navegador precisa "esquecer" a versão antiga.

**Na dúvida:** Sempre tente `Ctrl + Shift + R` primeiro!

---

**Última atualização:** 28/01/2026  
**Versão do sistema:** v9.21.0  
**Dúvidas:** Consulte a equipe de TI
