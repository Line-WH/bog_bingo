const grid = document.querySelector('#bingoGrid');
const totalCountEl = document.querySelector('#totalCount');
const checkedCountEl = document.querySelector('#checkedCount');
const progressBar = document.querySelector('#progressBar');
const progressPctEl = document.querySelector('#progressPct');
const resetBtn = document.querySelector('#resetBtn');
const shareBtn = document.querySelector('#shareBtn');

let tasks = [];
const STORAGE_KEY = 'bogbingo_tasks_v2';

function loadTasks() {
    fetch('./tasks.json')
        .then(res => {
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            return res.json();
        })
        .then(data => {
            // gem tasks fra JSON
            tasks = data.map((t, i) => ({
                id: i + 1,
                text: t.text,
                img: t.img,
                done: false
            }));

            saveTasks();
            renderGrid();
            updateProgress();
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
        const cell = document.createElement('div');
        cell.className = 'bingo-cell' + (task.done ? ' completed' : '');
        cell.setAttribute('role', 'button');
        cell.tabIndex = 0;
        cell.setAttribute('aria-pressed', task.done);
        cell.setAttribute('aria-label', task.text);

        const img = document.createElement('img');
        img.src = task.img;
        img.alt = task.text;
        img.className = 'bingo-img img-fluid'; // <-- behold img-fluid her!
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
