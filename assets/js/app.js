const grid = document.querySelector('#bingoGrid');
const totalCountEl = document.querySelector('#totalCount');
const checkedCountEl = document.querySelector('#checkedCount');
const progressBar = document.querySelector('#progressBar');
const progressPctEl = document.querySelector('#progressPct');
const resetBtn = document.querySelector('#resetBtn');
const shareBtn = document.querySelector('#shareBtn');

let tasks = [];

function loadTasks() {
    // prøv at hente fra localStorage
    try { tasks = JSON.parse(localStorage.getItem('bogbingo_tasks_simple')) || []; } catch { tasks = []; }

    // hvis tomt, så hent fra tasks.json
    if (!tasks.length) {
        fetch('tasks.json')
            .then(res => res.json())
            .then(data => {
                tasks = data.map((t,i) => ({ id: i+1, text: t, done: false }));
                saveTasks(); renderGrid(); updateProgress();
            });
    } else {
        renderGrid(); updateProgress();
    }
}

function saveTasks() {
    localStorage.setItem('bogbingo_tasks_simple', JSON.stringify(tasks));
}

function updateProgress() {
    const total = tasks.length || 1;
    const done  = tasks.filter(t => t.done).length;
    const pct   = Math.round(done/total*100);

    totalCountEl.textContent = total;
    checkedCountEl.textContent = done;
    progressBar.style.width = pct + "%";
    progressBar.setAttribute('aria-valuenow', pct);
    progressPctEl.textContent = pct;

    // Gør progress-baren grøn når der er fremskridt
    progressBar.classList.toggle('bg-success', done > 0);
    progressBar.classList.toggle('bg-primary', done === 0);
}


function toggleTask(task, cell) {
    task.done = !task.done;
    cell.classList.toggle('completed', task.done);
    cell.setAttribute('aria-pressed', task.done);
    saveTasks(); updateProgress();
}

function renderGrid() {
    grid.innerHTML = '';
    tasks.forEach(task => {
        const cell = document.createElement('div');
        cell.className = 'bingo-cell' + (task.done ? ' completed' : '');
        cell.setAttribute('role','button');
        cell.tabIndex = 0;
        cell.setAttribute('aria-pressed', task.done);

        const text = document.createElement('span');
        text.className = 'label-text';
        text.textContent = task.text;
        cell.appendChild(text);

        const onToggle = () => toggleTask(task, cell);
        cell.addEventListener('click', onToggle);
        cell.addEventListener('keydown', e => {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); onToggle(); }
        });

        grid.appendChild(cell);
    });
}

resetBtn.addEventListener('click', () => {
    tasks.forEach(t => t.done = false);
    saveTasks(); renderGrid(); updateProgress();
});

shareBtn.addEventListener('click', () => {
    const total = tasks.length || 1;
    const done = tasks.filter(t=>t.done).length;
    const pct  = Math.round(done/total*100);
    const text = `Min Bogbingo-status: ${done}/${total} (${pct}%).`;
    navigator.share ? navigator.share({ title:'Bogbingo', text }) :
        navigator.clipboard.writeText(text).then(()=>alert('Status kopieret til udklipsholder!'));
});

// Init
loadTasks();
