// js/filtros.js - Sistema de filtros por data e comparação

const FiltroSistema = {
	dataInicio: null,
	dataFim: null,

	init() {
		this.setupEventListeners();
		this.setDatasPadrao();
	},

	setDatasPadrao() {
		const hoje = new Date();
		const uma_semana_atras = new Date(hoje);
		uma_semana_atras.setDate(hoje.getDate() - 7);

		document.getElementById("data-inicio").valueAsDate = uma_semana_atras;
		document.getElementById("data-fim").valueAsDate = hoje;
	},

	setupEventListeners() {
		const btnFiltrar = document.getElementById("btn-filtrar");
		if (btnFiltrar) {
			btnFiltrar.addEventListener("click", () => this.aplicarFiltro());
		}

		const btnResetFiltro = document.getElementById("btn-reset-filtro");
		if (btnResetFiltro) {
			btnResetFiltro.addEventListener("click", () => this.resetarFiltro());
		}
	},

	async aplicarFiltro() {
		this.dataInicio = document.getElementById("data-inicio").value;
		this.dataFim = document.getElementById("data-fim").value;

		if (!this.dataInicio || !this.dataFim) {
			alert("Por favor, selecione ambas as datas");
			return;
		}

		if (this.dataInicio > this.dataFim) {
			alert("Data de início não pode ser maior que data de fim");
			return;
		}

		try {
			const response = await fetch(
				`./api/filtros.php?data_inicio=${this.dataInicio}&data_fim=${this.dataFim}`,
			);
			const data = await response.json();

			if (data.success) {
				this.exibirResultados(data.data);
			} else {
				alert("Erro: " + data.message);
			}
		} catch (error) {
			console.error("Erro ao filtrar:", error);
			alert("Erro ao processar filtro");
		}
	},

	exibirResultados(dados) {
		const container = document.getElementById("filtro-resultados");
		if (!container) return;

		let html = `
            <div class="filtro-resultado-header">
                <h3>📊 Comparação do Período</h3>
                <p>${dados.periodo.inicio} até ${dados.periodo.fim}</p>
            </div>
            <div class="comparacao-grid">
        `;

		for (const [avatar, stats] of Object.entries(dados.comparacao)) {
			const crescimentoClass = stats.crescimento >= 0 ? "positivo" : "negativo";
			const setaDirecao = stats.crescimento >= 0 ? "📈" : "📉";

			html += `
                <div class="card-comparacao">
                    <h4>${avatar}</h4>
                    <div class="stat-linha">
                        <span>Início:</span>
                        <strong>${stats.seguidores_inicio}</strong>
                    </div>
                    <div class="stat-linha">
                        <span>Fim:</span>
                        <strong>${stats.seguidores_fim}</strong>
                    </div>
                    <div class="stat-linha crescimento ${crescimentoClass}">
                        <span>${setaDirecao} Crescimento:</span>
                        <strong>${stats.crescimento > 0 ? "+" : ""}${stats.crescimento} (${stats.percentual_crescimento}%)</strong>
                    </div>
                    <div class="stat-linha">
                        <span>Atualizações:</span>
                        <strong>${stats.quantidade_atualizacoes}</strong>
                    </div>
                </div>
            `;
		}

		html += `
            </div>
            <div class="filtro-resultado-total">
                <p><strong>Total de Crescimento:</strong> ${dados.total_crescimento > 0 ? "+" : ""}${dados.total_crescimento} seguidores</p>
            </div>
        `;

		container.innerHTML = html;
		container.style.display = "block";
	},

	resetarFiltro() {
		document.getElementById("filtro-resultados").style.display = "none";
		this.setDatasPadrao();
	},
};

// Inicializar ao carregar
document.addEventListener("DOMContentLoaded", () => FiltroSistema.init());
