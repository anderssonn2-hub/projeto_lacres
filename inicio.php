<?php
// Página inicial do sistema (corrigida)
// Mantém apenas os 4 quadrantes com seus cards.
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Início - Projeto Lacres v0.9.25.14</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: "Trebuchet MS", "Verdana", sans-serif;
      background: radial-gradient(circle at top, #f4f0ea 0%, #f2f6fb 45%, #eef1f5 100%);
      color: #1f2a35;
      min-height: 100vh;
      padding: 32px;
    }
    .page { max-width: 1100px; margin: 0 auto; }

    .header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      margin-bottom: 22px;
    }
    h1 {
      font-family: "Palatino Linotype", "Book Antiqua", Palatino, serif;
      font-size: 28px;
      color: #1b3a57;
      line-height: 1.1;
    }
    .sub {
      font-size: 13px;
      color: #51606f;
      margin-top: 4px;
    }
    .version {
      background: #1b3a57;
      color: #fff;
      padding: 6px 12px;
      border-radius: 14px;
      font-weight: 700;
      font-size: 12px;
      white-space: nowrap;
    }

    .quadrants {
      display: grid;
      grid-template-columns: repeat(2, minmax(240px, 1fr));
      gap: 18px;
    }
    .quadrant {
      background: #fff;
      border-radius: 16px;
      padding: 18px;
      box-shadow: 0 12px 30px rgba(0,0,0,0.12);
      border: 1px solid #e1e5ea;
    }
    .quadrant h2 {
      font-size: 14px;
      letter-spacing: 1px;
      text-transform: uppercase;
      color: #6b7a88;
      margin-bottom: 12px;
    }
    .actions { display: grid; gap: 10px; }

    a.btn {
      display: flex;
      flex-direction: column;
      gap: 4px;
      text-decoration: none;
      padding: 14px 16px;
      border-radius: 12px;
      color: #fff;
      font-weight: 700;
      min-height: 84px;
      justify-content: center;
      box-shadow: 0 6px 14px rgba(0,0,0,0.14);
    }
    .btn span {
      font-size: 12px;
      font-weight: 500;
      opacity: 0.95;
    }

    .btn-conf { background: linear-gradient(135deg, #2f80ed 0%, #56ccf2 100%); }
    .btn-vocal { background: linear-gradient(135deg, #c62828 0%, #ef5350 100%); }
    .btn-lacres { background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%); color: #4a3200; }
    .btn-lacres-test { background: linear-gradient(135deg, #00796b 0%, #26a69a 100%); }
    .btn-oficio { background: linear-gradient(135deg, #0f2027 0%, #2c5364 100%); }
    .btn-bloq { background: linear-gradient(135deg, #512da8 0%, #7e57c2 100%); }
    .btn-cons { background: linear-gradient(135deg, #00b09b 0%, #96c93d 100%); }
    .btn-devol { background: linear-gradient(135deg, #ff6f00 0%, #ffb300 100%); }

    /* ── Card Meu Turno ───────────────────────── */
    .turno-card {
      background: #fff;
      border-radius: 16px;
      padding: 18px 22px;
      box-shadow: 0 12px 30px rgba(0,0,0,0.12);
      border: 1px solid #e1e5ea;
      margin-top: 18px;
    }
    .turno-card h2 {
      font-size: 14px;
      letter-spacing: 1px;
      text-transform: uppercase;
      color: #6b7a88;
      margin-bottom: 14px;
    }
    .turno-corpo {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 22px;
    }
    .turno-info-col { min-width: 160px; }
    .turno-hora { font-size: 12px; color: #8a9bac; margin-bottom: 3px; }
    .turno-atual { font-size: 17px; font-weight: 700; color: #1b3a57; }
    .turno-nota { font-size: 11px; color: #8a9bac; margin-top: 5px; }
    .turno-btns { display: flex; gap: 8px; flex-wrap: wrap; }
    .turno-btn {
      padding: 9px 18px;
      border: 2px solid #d0d7df;
      border-radius: 10px;
      background: #f5f7fa;
      cursor: pointer;
      font-weight: 600;
      font-size: 13px;
      color: #444;
      transition: border-color .15s, background .15s, color .15s;
      user-select: none;
    }
    .turno-btn:hover { border-color: #1b3a57; color: #1b3a57; }
    .turno-btn.ativo {
      background: #1b3a57;
      color: #fff;
      border-color: #1b3a57;
      box-shadow: 0 4px 12px rgba(27,58,87,.3);
    }
    .turno-operador {
      font-size: 12px;
      color: #8a9bac;
      margin-top: 10px;
      padding-top: 10px;
      border-top: 1px solid #eee;
    }
    @media (max-width: 900px) {
      body { padding: 18px; }
      .quadrants { grid-template-columns: 1fr; }
      .turno-corpo { flex-direction: column; align-items: flex-start; }
    }
  </style>
</head>
<body>
  <div class="page">
    <div class="header">
      <div>
        <h1>Projeto Lacres</h1>
        <div class="sub">Selecione um tema e inicie sua rotina.</div>
      </div>
      <div class="version">v0.9.25.14</div>
    </div>

    <div class="quadrants">
      <section class="quadrant">
        <h2>Operacao</h2>
        <div class="actions">
          <a class="btn btn-conf" href="conferencia_pacotes.php">
            Iniciar conferencia
            <span>Leitura e validacao de pacotes</span>
          </a>
          <a class="btn btn-vocal" href="encontra_posto.php">
            Quem eu sou?
            <span>Leitura rapida com voz</span>
          </a>
        </div>
      </section>

      <section class="quadrant">
        <h2>Lacres e oficios</h2>
        <div class="actions">
          <a class="btn btn-lacres" href="lacres_novo.php">
            Lacres e oficios Correios
            <span>Fluxo completo com regras atuais</span>
          </a>
          <a class="btn btn-lacres-test" href="lacres_correios_teste.php">
            Lacres Correios (em teste)
            <span>Planilha simplificada e flexivel</span>
          </a>
          <a class="btn btn-oficio" href="gera_oficio_poupa_tempo.php">
            Gerar oficio Poupa Tempo
            <span>Selecionar datas e imprimir</span>
          </a>
        </div>
      </section>

      <section class="quadrant">
        <h2>Administracao</h2>
        <div class="actions">
          <a class="btn btn-bloq" href="bloqueados.php">
            Bloqueio de postos
            <span>Definir postos nao enviados</span>
          </a>
          <a class="btn btn-cons" href="consulta_producao.php">
            Consultar producao
            <span>Busca por lotes, postos e datas</span>
          </a>
        </div>
      </section>

      <section class="quadrant">
        <h2>Etiquetas Correios</h2>
        <div class="actions">
          <a class="btn btn-devol" href="devolucao_etiquetas.php">
            Devolucao de etiquetas
            <span>Registrar retorno de malotes</span>
          </a>
        </div>
      </section>
    </div><!-- /.quadrants -->

    <div class="turno-card" id="turnoCard">
      <h2>🕐 Meu Turno</h2>
      <div class="turno-corpo">
        <div class="turno-info-col">
          <div class="turno-hora" id="turnoHora">—</div>
          <div class="turno-atual" id="turnoAtualLabel">—</div>
          <div class="turno-nota">Preferência salva no navegador · pré-carregada na conferência</div>
        </div>
        <div class="turno-btns">
          <button class="turno-btn" data-turno="Madrugada" onclick="selecionarTurno('Madrugada')">🌙 Madrugada</button>
          <button class="turno-btn" data-turno="Manhã" onclick="selecionarTurno('Manhã')">🌅 Manhã</button>
          <button class="turno-btn" data-turno="Tarde" onclick="selecionarTurno('Tarde')">☀️ Tarde</button>
          <button class="turno-btn" data-turno="Noite" onclick="selecionarTurno('Noite')">🌆 Noite</button>
        </div>
      </div>
      <div class="turno-operador" id="turnoOperador"></div>
    </div>

  </div><!-- /.page -->
</body>
<script>
(function() {
  var TURNO_KEY  = 'projeto_lacres_turno';
  var USUARIO_KEY = 'conferencia_responsavel';

  function detectarTurnoAuto() {
    var h = new Date().getHours();
    if (h < 6)  return 'Madrugada';
    if (h < 12) return 'Manhã';
    if (h < 18) return 'Tarde';
    return 'Noite';
  }

  function turnoLabel(t) {
    if (t === 'Madrugada') return '🌙 Madrugada';
    if (t === 'Manhã')     return '🌅 Manhã';
    if (t === 'Tarde')     return '☀️ Tarde';
    if (t === 'Noite')     return '🌆 Noite';
    return '⏰ ' + t;
  }

  function atualizarBtns(turno) {
    document.querySelectorAll('.turno-btn').forEach(function(btn) {
      btn.classList.toggle('ativo', btn.getAttribute('data-turno') === turno);
    });
    var lbl = document.getElementById('turnoAtualLabel');
    if (lbl) lbl.textContent = turnoLabel(turno);
  }

  window.selecionarTurno = function(turno) {
    try { localStorage.setItem(TURNO_KEY, turno); } catch(e) {}
    atualizarBtns(turno);
  };

  function atualizarHora() {
    var el = document.getElementById('turnoHora');
    if (!el) return;
    var now  = new Date();
    var dias = ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'];
    var h    = String(now.getHours()).padStart(2,'0');
    var m    = String(now.getMinutes()).padStart(2,'0');
    el.textContent = dias[now.getDay()] + ', ' + h + ':' + m;
  }

  /* init */
  var turnoSalvo = '';
  try { turnoSalvo = localStorage.getItem(TURNO_KEY) || ''; } catch(e) {}
  var turno = turnoSalvo || detectarTurnoAuto();
  if (!turnoSalvo) { try { localStorage.setItem(TURNO_KEY, turno); } catch(e) {} }
  atualizarBtns(turno);
  atualizarHora();
  setInterval(atualizarHora, 30000);

  /* operador (só disponível se ainda na mesma aba) */
  var operador = '';
  try { operador = sessionStorage.getItem(USUARIO_KEY) || ''; } catch(e) {}
  var opEl = document.getElementById('turnoOperador');
  if (opEl) {
    opEl.textContent = operador
      ? 'Operador: ' + operador
      : 'Operador: (será solicitado ao iniciar a conferência)';
  }
})();
</script>
</html>
