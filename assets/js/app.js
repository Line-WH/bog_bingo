const grid = document.querySelector('#bingoGrid');
const totalCountEl = document.querySelector('#totalCount');
const checkedCountEl = document.querySelector('#checkedCount');
const progressBar = document.querySelector('#progressBar');
const progressPctEl = document.querySelector('#progressPct');
const resetBtn = document.querySelector('#resetBtn');
const shareBtn = document.querySelector('#shareBtn');

let tasks = []; // <- kun én gang!
const STORAGE_KEY = 'bogbingo_tasks_v2';

function loadTasks() {
    //try { tasks = JSON.parse(localStorage.getItem(STORAGE_KEY)) || []; }
    //catch { tasks = []; }


    fetch('./tasks.json')  // sørg for at tasks.json ligger i samme mappe som HTML’en
        .then(res => {
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            return res.json();
        })
        .then(data => {
            if(!data){
                return;
            }

            for(const task of data){
                const div = document.createElement('div');
                div.innerHTML = `
                    <p>${task.text}</p>
                    <img class="img-fluid" src="${task.img}">
                `;

                grid.appendChild(div);
            }

            saveTasks();
            //renderGrid();
            //updateProgress();
        })
        .catch(err => {
            console.error('Kunne ikke loade tasks.json:', err);
        });

}

function saveTasks() {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(tasks));
}

function updateProgress() {
    const total = tasks.length || 1;
    const done = tasks.filter(t => t.done).length;
    const pct = Math.round(done / total * 100);

    totalCountEl.textContent = total;
    checkedCountEl.textContent = done;
    progressBar.style.width = pct + '%';
    progressBar.setAttribute('aria-valuenow', pct);
    progressPctEl.textContent = pct;

    progressBar.classList.toggle('bg-success', done > 0);
    progressBar.classList.toggle('bg-primary', done === 0);
}

function toggleTask(task, cell) {
    task.done = !task.done;
    cell.classList.toggle('completed', task.done);
    cell.setAttribute('aria-pressed', task.done);
    saveTasks();
    updateProgress();
}

function renderGrid() {
    grid.innerHTML = '';
    tasks.forEach(task => {
        const cell = document.querySelector('div');
        cell.className = 'bingo-cell' + (task.done ? ' completed' : '');
        cell.setAttribute('role', 'button');
        cell.tabIndex = 0;
        cell.setAttribute('aria-pressed', task.done);
        cell.setAttribute('aria-label', task.text);

        const img = document.createElement('img');
        img.src = task.img || `https://picsum.photos/seed/${encodeURIComponent(task.text)}/400/260`;
        img.alt = task.text;
        img.className = 'bingo-img';
        cell.appendChild(img);

        const text = document.createElement('span');
        text.className = 'label-text';
        text.textContent = task.text;
        cell.appendChild(text);

        const onToggle = () => toggleTask(task, cell);
        cell.addEventListener('click', onToggle);
        cell.addEventListener('keydown', e => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                onToggle();
            }
        });

        grid.appendChild(cell);
    });
}

resetBtn.addEventListener('click', () => {
    tasks.forEach(t => t.done = false);
    saveTasks();
    renderGrid();
    updateProgress();
});

shareBtn.addEventListener('click', () => {
    const total = tasks.length || 1;
    const done = tasks.filter(t => t.done).length;
    const pct = Math.round(done / total * 100);
    const text = `Min Bogbingo-status: ${done}/${total} (${pct}%).`;
    if (navigator.share) navigator.share({title: 'Bogbingo', text});
    else navigator.clipboard.writeText(text).then(() => alert('Status kopieret til udklipsholder!'));
});

// Init
loadTasks();
