function copyLink(url) {
    navigator.clipboard.writeText(url).then(() => {
        alert('Link copiado para a área de transferência!');
    }).catch(err => {
        console.error('Erro ao copiar link: ', err);
        alert('Erro ao copiar link. Tente copiar manualmente.');
    });
}

function openAtsModal(id) {
    document.getElementById('atsResumeId').value = id;
    const modal = document.getElementById('atsModal');
    modal.classList.remove('hidden');
    modal.style.display = 'flex';
    document.getElementById('atsResult').classList.add('hidden');
    document.getElementById('jobDescription').value = '';
}

function closeAtsModal() {
    document.getElementById('atsModal').classList.add('hidden');
    document.getElementById('atsModal').style.display = 'none';
}

async function analyzeAts() {
    const id = document.getElementById('atsResumeId').value;
    const jobDescription = document.getElementById('jobDescription').value;
    const btn = document.getElementById('btnAnalyze');

    if (!jobDescription) return alert('Por favor, cole a descrição da vaga.');

    btn.disabled = true;
    btn.innerText = 'Analisando com I.A...';

    try {
        // 1. Get Resume Data from our API
        const formData = new FormData();
        formData.append('resume_id', id);

        const response = await fetch('ats-analysis.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (!data.success) throw new Error(data.error);

        // 2. Use Puter AI (gpt-4o-mini) to analyze
        const prompt = `
        Aja como um recrutador técnico experiente e especialista em sistemas ATS.
        Analise o currículo abaixo em relação à descrição da vaga fornecida.
        
        CURRÍCULO:
        ${data.resume_text}
        
        VAGA:
        ${jobDescription}
        
        Responda EXCLUSIVAMENTE em formato JSON com a seguinte estrutura:
        {
            "score": (número de 0 a 100),
            "matches": ["palavra-chave1", "habilidade2", ...],
            "missing": ["palavra-chave_ausente1", "requisito_faltante2", ...]
        }
        A lista "matches" deve conter o que o candidato já tem que é relevante para a vaga.
        A lista "missing" deve conter palavras-chave ou habilidades essenciais da vaga que NÃO estão no currículo.
        Seja criterioso no score.
        `;

        const aiResponse = await puter.ai.chat(prompt, { model: 'gpt-4o-mini' });
        
        // Clean AI response if needed (sometimes it wraps in ```json)
        let cleanContent = aiResponse.toString().trim();
        if (cleanContent.startsWith('```json')) {
            cleanContent = cleanContent.replace(/^```json/, '').replace(/```$/, '').trim();
        } else if (cleanContent.startsWith('```')) {
             cleanContent = cleanContent.replace(/^```/, '').replace(/```$/, '').trim();
        }

        const result = JSON.parse(cleanContent);

        // 3. Update UI
        document.getElementById('atsScore').innerText = result.score + '%';

        const matchesDiv = document.getElementById('atsMatches');
        matchesDiv.innerHTML = result.matches.map(m => `<span class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-3 py-1.5 rounded-lg">${m}</span>`).join('');

        const missingDiv = document.getElementById('atsMissing');
        missingDiv.innerHTML = result.missing.map(m => `<span class="bg-red-500/10 border border-red-500/20 text-red-400 px-3 py-1.5 rounded-lg font-bold">${m}</span>`).join('');

        document.getElementById('atsResult').classList.remove('hidden');
        document.getElementById('atsResult').style.display = 'block';

    } catch (err) {
        console.error('Erro na análise ATS:', err);
        alert('Erro na análise: ' + err.message);
    } finally {
        btn.disabled = false;
        btn.innerText = 'Analisar Agora';
    }
}
