/**
 * tiktok-manager.js
 * Gerenciar autenticação e busca de seguidores do TikTok
 */

class TikTokManager {
	constructor() {
		this.baseUrl = "api/tiktok-oauth.php";
		this.fetchUrl = "api/fetch-followers.php";
		this.avatares = ["Ana", "Megg", "Bia", "Luna", "Mel"];
	}

	/**
	 * Iniciar processo de autenticação
	 */
	async authenticate(avatar) {
		try {
			const response = await fetch(
				`${this.baseUrl}?action=authorize&avatar=${encodeURIComponent(avatar)}`,
			);
			const data = await response.json();

			if (data.success && data.data.auth_url) {
				// Redirecionar para o TikTok
				window.location.href = data.data.auth_url;
			} else {
				alert("Erro: " + data.message);
			}
		} catch (error) {
			console.error("Erro ao autenticar:", error);
			alert("Erro ao iniciar autenticação: " + error.message);
		}
	}

	/**
	 * Buscar seguidores de um avatar
	 */
	async fetchFollowers(avatar) {
		try {
			const btn = document.querySelector(
				`[data-action="fetch"][data-avatar="${avatar}"]`,
			);
			if (btn) btn.disabled = true;

			const response = await fetch(this.fetchUrl, {
				method: "POST",
				headers: { "Content-Type": "application/json" },
				body: JSON.stringify({ avatar }),
			});

			const data = await response.json();

			if (data.success) {
				const result = data.data[avatar];
				if (result.success) {
					this.showSuccess(`${avatar}: ${result.data.seguidores} seguidores`);
					this.refreshUI();
				} else {
					alert(`Erro: ${result.message}`);
				}
			} else {
				alert("Erro: " + data.message);
			}

			if (btn) btn.disabled = false;
		} catch (error) {
			console.error("Erro ao buscar seguidores:", error);
			alert("Erro ao buscar seguidores: " + error.message);
		}
	}

	/**
	 * Buscar seguidores de todos os avatares
	 */
	async fetchAllFollowers() {
		try {
			const btn = document.querySelector('[data-action="fetch-all"]');
			if (btn) btn.disabled = true;

			const response = await fetch(this.fetchUrl, {
				method: "POST",
				headers: { "Content-Type": "application/json" },
				body: JSON.stringify({ refresh_all: true }),
			});

			const data = await response.json();

			if (data.success) {
				let successCount = 0;
				let errorCount = 0;

				for (const [avatar, result] of Object.entries(data.data)) {
					if (result.success) {
						successCount++;
					} else {
						errorCount++;
					}
				}

				this.showSuccess(
					`Busca concluída: ${successCount} sucesso(s), ${errorCount} erro(s)`,
				);
				this.refreshUI();
			} else {
				alert("Erro: " + data.message);
			}

			if (btn) btn.disabled = false;
		} catch (error) {
			console.error("Erro ao buscar seguidores:", error);
			alert("Erro ao buscar seguidores: " + error.message);
		}
	}

	/**
	 * Carregar status de autenticação
	 */
	async loadStatus() {
		try {
			const response = await fetch(this.fetchUrl);
			const data = await response.json();

			if (data.success) {
				this.updateStatusDisplay(data.data);
			}
		} catch (error) {
			console.error("Erro ao carregar status:", error);
		}
	}

	/**
	 * Atualizar display de status
	 */
	updateStatusDisplay(perfis) {
		const container = document.getElementById("status-container");
		if (!container) return;

		container.innerHTML = "";

		for (const perfil of perfis) {
			const status = perfil.autenticado ? "✅ Autenticado" : "⚠️ Não autenticado";
			const tokenStatus = perfil.token_expirado ? "⏰ Expirado" : "🔄 Válido";

			const html = `
                <div class="status-item">
                    <strong>${perfil.avatar_nome}</strong><br>
                    Status: ${status}<br>
                    Token: ${tokenStatus}<br>
                    ${perfil.tiktok_username ? `Usuário: ${perfil.tiktok_username}<br>` : ""}
                    <button onclick="tikTokManager.authenticate('${perfil.avatar_nome}')" class="btn-small">
                        ${perfil.autenticado ? "Renovar" : "Autenticar"}
                    </button>
                    ${
																					perfil.autenticado && !perfil.token_expirado
																						? `<button onclick="tikTokManager.fetchFollowers('${perfil.avatar_nome}')" class="btn-small">
                            Buscar Agora
                        </button>`
																						: ""
																				}
                </div>
            `;

			container.innerHTML += html;
		}
	}

	/**
	 * Atualizar UI
	 */
	refreshUI() {
		this.loadStatus();

		// Recarregar dashboard se existir
		if (
			window.location.pathname === "/" ||
			window.location.pathname === "/index.php"
		) {
			location.reload();
		}
	}

	/**
	 * Mostrar mensagem de sucesso
	 */
	showSuccess(message) {
		const div = document.createElement("div");
		div.className = "success-message";
		div.textContent = message;
		div.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #00f7ef;
            color: #000;
            padding: 15px 20px;
            border-radius: 5px;
            z-index: 10000;
            box-shadow: 0 2px 10px rgba(0, 247, 239, 0.3);
        `;
		document.body.appendChild(div);

		setTimeout(() => div.remove(), 3000);
	}

	/**
	 * Carregar e exibir status "Ao Vivo"
	 */
	async loadLiveStatus() {
		try {
			// Fazer requisição para buscar dados recentes
			const response = await fetch("api/fetch-followers.php");
			const data = await response.json();

			if (!data.success) {
				console.error("Erro ao carregar status ao vivo");
				return;
			}

			// Buscar últimos registros para cada avatar
			const response2 = await fetch("api/check-live-status.php");
			const liveData = response2.ok ? await response2.json() : { data: {} };

			// Contar avatares ao vivo
			let aoVivoCount = 0;
			let offlineCount = 0;
			let htmlList = "";

			for (const avatar of this.avatares) {
				const isLive =
					liveData.data &&
					liveData.data[avatar] &&
					liveData.data[avatar].ao_vivo === 1;
				const seguidores =
					liveData.data && liveData.data[avatar]
						? liveData.data[avatar].seguidores
						: 0;

				if (isLive) {
					aoVivoCount++;
					htmlList += `<div class="ao-vivo-item">
						<span>🔴 <strong>${avatar}</strong> - ${seguidores} seguidores</span>
						<small>${new Date(liveData.data[avatar].data).toLocaleTimeString("pt-BR")}</small>
					</div>`;
				} else {
					offlineCount++;
					htmlList += `<div class="ao-vivo-item offline">
						<span>⚫ <strong>${avatar}</strong> - ${seguidores} seguidores</span>
						<small>${new Date(liveData.data[avatar].data).toLocaleTimeString("pt-BR")}</small>
					</div>`;
				}
			}

			// Atualizar UI
			const aoVivoCountEl = document.getElementById("ao-vivo-count");
			const offlineCountEl = document.getElementById("offline-count");
			const aoVivoListEl = document.getElementById("ao-vivo-list");

			if (aoVivoCountEl) aoVivoCountEl.textContent = aoVivoCount;
			if (offlineCountEl) offlineCountEl.textContent = offlineCount;
			if (aoVivoListEl) aoVivoListEl.innerHTML = htmlList;
		} catch (error) {
			console.error("Erro ao carregar status ao vivo:", error);
		}
	}
}
