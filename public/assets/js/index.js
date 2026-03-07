function openNicheModal() {
    document.getElementById('nicheModal').style.display = 'flex';
}

function closeNicheModal() {
    document.getElementById('nicheModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function (event) {
    const modal = document.getElementById('nicheModal');
    if (event.target == modal) {
        closeNicheModal();
    }
}
