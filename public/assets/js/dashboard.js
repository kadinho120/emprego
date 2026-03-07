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

function analyzeAts() {
    const id = document.getElementById('atsResumeId').value;
    const desc = document.getElementById('jobDescription').value;
    const btn = document.getElementById('btnAnalyze');

    if (!desc) return alert('Por favor, cole a descrição da vaga.');

    btn.disabled = true;
    btn.innerText = 'Analisando...';

    const formData = new FormData();
    formData.append('resume_id', id);
    formData.append('job_description', desc);

    fetch('ats-analysis.php', {
        method: 'POST',
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            if (data.error) throw new Error(data.error);

            document.getElementById('atsScore').innerText = data.score + '%';

            const matchesDiv = document.getElementById('atsMatches');
            matchesDiv.innerHTML = data.matches.map(m => `<span class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-3 py-1.5 rounded-lg">${m}</span>`).join('');

            const missingDiv = document.getElementById('atsMissing');
            missingDiv.innerHTML = data.missing.map(m => `<span class="bg-red-500/10 border border-red-500/20 text-red-400 px-3 py-1.5 rounded-lg font-bold">${m}</span>`).join('');

            const resDiv = document.getElementById('atsResult');
            resDiv.classList.remove('hidden');
            resDiv.style.display = 'block';
        })
        .catch(err => alert('Erro na análise: ' + err.message))
        .finally(() => {
            btn.disabled = false;
            btn.innerText = 'Analisar Agora';
        });
}
