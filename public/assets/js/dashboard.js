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
    document.getElementById('atsModal').style.display = 'flex';
    document.getElementById('atsResult').style.display = 'none';
    document.getElementById('jobDescription').value = '';
}

function closeAtsModal() {
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
            matchesDiv.innerHTML = data.matches.map(m => `<span class="tag tag-match">${m}</span>`).join('');

            const missingDiv = document.getElementById('atsMissing');
            missingDiv.innerHTML = data.missing.map(m => `<span class="tag tag-missing">${m}</span>`).join('');

            document.getElementById('atsResult').style.display = 'block';
        })
        .catch(err => alert('Erro na análise: ' + err.message))
        .finally(() => {
            btn.disabled = false;
            btn.innerText = 'Analisar Agora';
        });
}
