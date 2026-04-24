// js/achievements.js - Sistema de badges e notificações

class AchievementSistema {
	constructor() {
		this.badges = {};
	}

	async init() {
		await this.carregarBadges();
		this.exibirNotificacoes();
	}

	async carregarBadges() {
		try {
			const response = await fetch("./api/achievements.php");
			const data = await response.json();

			if (data.success) {
				this.badges = data.data;
			}
		} catch (error) {
			console.error("Erro ao carregar achievements:", error);
		}
	}

	exibirNotificacoes() {
		for (const [avatar, dados] of Object.entries(this.badges)) {
			// Notificação de meta atingida
			if (dados.badges.some((b) => b.id === "meta_atingida")) {
				this.mostrarNotificacao(
					`🏆 ${avatar} atingiu a meta de 2.000 seguidores!`,
					"success",
				);
			}

			// Notificação de crescimento rápido
			if (dados.badges.some((b) => b.id === "crescimento_rapido")) {
				this.mostrarNotificacao(`🚀 ${avatar} teve crescimento rápido!`, "info");
			}
		}
	}

	mostrarNotificacao(mensagem, tipo = "info") {
		const notificacaoDiv = document.createElement("div");
		notificacaoDiv.className = `notificacao notificacao-${tipo}`;
		notificacaoDiv.textContent = mensagem;
		notificacaoDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            background: ${tipo === "success" ? "#10b981" : "#3b82f6"};
            color: white;
            font-weight: 600;
            z-index: 9999;
            animation: slideIn 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        `;

		document.body.appendChild(notificacaoDiv);

		setTimeout(() => {
			notificacaoDiv.style.animation = "slideOut 0.3s ease";
			setTimeout(() => notificacaoDiv.remove(), 300);
		}, 5000);
	}

	renderizarBadgesParaAvatar(nomeavatar) {
		const dados = this.badges[nomeavatar];
		if (!dados || dados.badges.length === 0) return "";

		let html = '<div class="badges-container">';
		for (const badge of dados.badges) {
			html += `<span class="badge" title="${badge.descricao}">${badge.icone}</span>`;
		}
		html += "</div>";
		return html;
	}
}

// Inicializar achievement system
const achievementSistema = new AchievementSistema();
document.addEventListener("DOMContentLoaded", () => achievementSistema.init());

// Animações CSS (adicionar ao style.css depois)
const style = document.createElement("style");
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);
