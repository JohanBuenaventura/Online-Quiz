// session.js: polls session state and renders UI, submits answer via AJAX
(function(){
    const code = (new URLSearchParams(window.location.search)).get('code');
    if (!code) return;
    const apiState = 'api_state.php?code=' + encodeURIComponent(code);
    const apiSubmit = 'submit_answer_ajax.php';
    const apiLeaderboard = 'api_leaderboard.php?code=' + encodeURIComponent(code);

    let lastQuestionId = null;

    function el(id){return document.getElementById(id)}

    async function fetchState(){
        try{
            const res = await fetch(apiState,{cache:'no-store'});
            if (!res.ok) throw new Error('No state');
            const data = await res.json();
            renderState(data);
        }catch(e){
            console.error(e);
        }
    }

    function clearChoices(){
        const container = el('choices');
        container.innerHTML = '';
    }

    function renderState(data){
        const q = data.current_question;
        const title = el('quiz_title');
        const codeEl = el('session_code');
        title.textContent = data.quiz_title || 'Quiz';
        codeEl.textContent = data.session_code || '';
        if (!q){
            el('card').classList.remove('live');
            el('question_text').textContent = 'Waiting for the teacher to start / show a question...';
            clearChoices();
            return;
        }
        // new question
        if (lastQuestionId !== q.id) {
            lastQuestionId = q.id;
            el('question_text').textContent = q.question_text;
            renderChoices(q);
        }
        // update leaderboard snapshot if provided
        if (data.leaderboard){ renderLeaderboard(data.leaderboard); }
    }

    function renderChoices(q){
        const container = el('choices');
        container.innerHTML = '';
        if (q.question_type === 'mcq'){
            q.choices.forEach(choice => {
                const btn = document.createElement('button');
                btn.className = 'choice outline';
                btn.textContent = choice.choice_text;
                btn.dataset.choiceId = choice.id;
                btn.onclick = () => submitChoice(choice.id);
                container.appendChild(btn);
            });
        } else if (q.question_type === 'tf'){
            ['True','False'].forEach((t,i)=>{
                const btn = document.createElement('button');
                btn.className = 'choice outline';
                btn.textContent = t;
                btn.dataset.value = t.toLowerCase();
                btn.onclick = () => submitTF(t.toLowerCase());
                container.appendChild(btn);
            });
        } else {
            const ta = document.createElement('textarea');
            ta.id = 'open_answer';
            ta.rows = 3; ta.style.width = '100%';
            const submit = document.createElement('button');
            submit.textContent = 'Submit';
            submit.onclick = () => submitOpen(ta.value);
            container.appendChild(ta); container.appendChild(submit);
        }
    }

    async function submitChoice(choiceId){
        try{
            const body = new URLSearchParams();
            body.append('session_id', el('session_id').value);
            body.append('question_id', lastQuestionId);
            body.append('choice_id', choiceId);

            const res = await fetch(apiSubmit,{method:'POST',body});
            const data = await res.json();
            if (data.success){
                disableChoices();
                fetchLeaderboard();
            } else alert(data.message||'Error');
        }catch(e){console.error(e);}
    }
    async function submitTF(value){
        try{
            const body = new URLSearchParams();
            body.append('session_id', el('session_id').value);
            body.append('question_id', lastQuestionId);
            body.append('choice_text', value);
            const res = await fetch(apiSubmit,{method:'POST',body});
            const data = await res.json();
            if (data.success){ disableChoices(); fetchLeaderboard(); } else alert(data.message||'Error');
        }catch(e){console.error(e);}
    }
    async function submitOpen(text){
        try{
            const body = new URLSearchParams();
            body.append('session_id', el('session_id').value);
            body.append('question_id', lastQuestionId);
            body.append('answer_text', text);
            const res = await fetch(apiSubmit,{method:'POST',body});
            const data = await res.json();
            if (data.success){ disableChoices(); fetchLeaderboard(); } else alert(data.message||'Error');
        }catch(e){console.error(e);}
    }

    function disableChoices(){
        Array.from(document.querySelectorAll('.choice, textarea, .choice button')).forEach(n=>n.disabled=true);
    }

    async function fetchLeaderboard(){
        try{
            const r = await fetch(apiLeaderboard,{cache:'no-store'});
            if (!r.ok) return;
            const d = await r.json();
            renderLeaderboard(d);
        }catch(e){console.error(e);}
    }

    function renderLeaderboard(list){
        const elb = document.getElementById('leaderboard_list');
        if (!elb) return;
        elb.innerHTML = '';
        if (!list || list.length === 0){ elb.innerHTML = '<div class="empty">No participants yet</div>'; return; }
        list.forEach((row, idx)=>{
            const div = document.createElement('div');
            div.className = 'leaderboard-row';
            div.innerHTML = '<div>' + (idx+1) + '. ' + (row.name || 'Guest') + '</div><div>' + (row.score||0) + '</div>';
            elb.appendChild(div);
        });
    }

    // initial load + polling
    fetchState();
    setInterval(fetchState, 3000);
    setInterval(fetchLeaderboard, 5000);
})();
