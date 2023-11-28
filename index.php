<link rel="stylesheet" href="styles.css">
<?php


?>

<div id="asstgpt-dialog-container">
  <div id="asstgpt-dialog"></div>
  <input id="asstgpt-input" type="text" placeholder="ENTER YOUR MESSAGE">
  <button id="asstgpt-submit">Send</button>
</div>

<script>
  const maxTokens = <?php echo json_encode($max_tokens); ?>;
  const assistantId = '<?php echo $assistant_id; ?>';
  const openaiApiKey = '<?php echo $openai_api_key; ?>';
  const instructions = '<?php echo $instructions; ?>';
  const intro_question_1 = <?php echo json_encode($intro_question_1); ?>;
  const intro_question_2 = <?php echo json_encode($intro_question_2); ?>;
  const intro_question_3 = <?php echo json_encode($intro_question_3); ?>;
  
  let isFirstMessageSent = false;
  /*
    Step 2: Create a Thread

    curl https://api.openai.com/v1/threads 
    -H "Content-Type: application/json" 
    -H "Authorization: Bearer $OPENAI_API_KEY" 
    -H "OpenAI-Beta: assistants=v1" 
    -d ''

  */

  function isMobileDevice() {
    return window.innerWidth <= 600;
  }

  var threadId = '';

  fetch(`https://api.openai.com/v1/threads`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'OpenAI-Beta': 'assistants=v1',
      'Authorization': 'Bearer ' + openaiApiKey
    },
  })
  .then(response => response.json())
  .then(data => {
    threadId = data.id;
  });

  const dialog = document.getElementById('asstgpt-dialog');
  const input = document.getElementById('asstgpt-input');

  function assRun(runId) {
    fetch(`https://api.openai.com/v1/threads/${threadId}/runs/${runId}`, {
      method: 'GET',
      headers: {
        'OpenAI-Beta': 'assistants=v1',
        'Authorization': 'Bearer ' + openaiApiKey
      }
    })
    .then(response => response.json())
    .then(data => {
      var status = data.status;
      /*
        curl https://api.openai.com/v1/threads/thread_abc123/messages 
          -H "Content-Type: application/json" 
          -H "Authorization: Bearer $OPENAI_API_KEY" 
          -H "OpenAI-Beta: assistants=v1"
        */
      if (data.status != 'completed') {
        assRun(runId);
      } else {
        fetch(`https://api.openai.com/v1/threads/${threadId}/messages`, {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
            'OpenAI-Beta': 'assistants=v1',
            'Authorization': 'Bearer ' + openaiApiKey
          }
        })
        .then(response => response.json())
        .then(data => {
          
          // dialog.removeChild(loadingDots);
          if (data.data && data.data.length > 0) {
            const gptResponse = data.data[0].content[0].text.value.trim();
            console.log(gptResponse);
            const gptMessage = document.createElement('div');
            gptMessage.classList.add('gpt-message');
            gptMessage.textContent = gptResponse;
            dialog.appendChild(gptMessage);
            dialog.scrollTop = dialog.scrollHeight;
          }

        })
      }

    });
  }

  // Функция для отправки сообщения и получения ответа от GPT-3.5
  function sendChatGPTMessage() {
    removeIntroQuestions();

    if (input.value.trim()) {
      const message = document.createElement('div');
      message.classList.add('user-message');
      message.textContent = input.value;
      dialog.appendChild(message);

      var loadingDots = document.createElement('div');
      loadingDots.classList.add('loading-dots-container');
      for (let i = 0; i < 3; i++) {
        const dot = document.createElement('div');
        dot.classList.add('loading-dot');
        loadingDots.appendChild(dot);
      }
      dialog.appendChild(loadingDots);

      /*
        Step 3: Add a Message to a Thread
        curl https://api.openai.com/v1/threads/thread_abc123/messages 
          -H "Content-Type: application/json" 
          -H "Authorization: Bearer $OPENAI_API_KEY" 
          -H "OpenAI-Beta: assistants=v1" 
          -d '{
              "role": "user",
              "content": "I need to solve the equation `3x + 11 = 14`. Can you help me?"
            }'
      */  

      fetch(`https://api.openai.com/v1/threads/${threadId}/messages`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'OpenAI-Beta': 'assistants=v1',
          'Authorization': 'Bearer ' + openaiApiKey
        },
        body: JSON.stringify({
          "role": "user",
          "content": input.value
        })
      })
      .then(response => response.json())
      .then(data => {
        
        /*
          Step 4: Run the Assistant

          curl https://api.openai.com/v1/threads/thread_abc123/runs 
            -H "Authorization: Bearer $OPENAI_API_KEY" 
            -H "Content-Type: application/json" 
            -H "OpenAI-Beta: assistants=v1" 
            -d '{
              "assistant_id": "asst_abc123",
              "instructions": "Please address the user as Jane Doe. The user has a premium account."
            }'
        */
        fetch(`https://api.openai.com/v1/threads/${threadId}/runs`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'OpenAI-Beta': 'assistants=v1',
            'Authorization': 'Bearer ' + openaiApiKey
          },
          body: JSON.stringify({
            "assistant_id": assistantId,
            "instructions": instructions
          })
        })
        .then(response => response.json())
        .then(data => {

          var runId = data.id;

          /*
              Step 5: Check the Run status
              curl https://api.openai.com/v1/threads/thread_abc123/runs/run_abc123 \
                -H "Authorization: Bearer $OPENAI_API_KEY" \
                -H "OpenAI-Beta: assistants=v1"

          */
          assRun(runId);
          dialog.removeChild(loadingDots);
        })



      });
      

      // fetch(`https://api.openai.com/v1/assistants/${assistantId}/messages`, {
      //   method: 'POST',
      //   headers: {
      //     'Content-Type': 'application/json',
      //     'Authorization': 'Bearer ' + openaiApiKey
      //   },
      //   body: JSON.stringify({
      //     model: "gpt-3.5-turbo-1106",
      //     inputs: input.value,
      //     max_tokens: maxTokens
      //   }),
      // })
      // .then(response => response.json())
      // .then(data => {

        /*dialog.removeChild(loadingDots);
        if (data.choices && data.choices.length > 0) {
          const gptResponse = data.choices[0].message.content.trim();
          const gptMessage = document.createElement('div');
          gptMessage.classList.add('gpt-message');
          gptMessage.textContent = gptResponse;
          dialog.appendChild(gptMessage);
          dialog.scrollTop = dialog.scrollHeight;
        }*/

      // })
      // .catch(err => {
      //   dialog.removeChild(loadingDots);
      //   console.error('Error:', err);
      // });
      // dialog.removeChild(loadingDots);
      input.value = '';
    }
  }

  document.getElementById('asstgpt-submit').addEventListener('click', sendChatGPTMessage);
  document.getElementById('asstgpt-input').addEventListener('keydown', function(event) {
    if (event.key === 'Enter') {
      event.preventDefault();
      sendChatGPTMessage();
    }
  });

  function removeIntroQuestions() {
    if (!isFirstMessageSent) {
      document.querySelectorAll('.intro-question').forEach(function(elem) {
        elem.remove();
      });
      isFirstMessageSent = true;
    }
  }

  window.addEventListener('DOMContentLoaded', function () {
    const isMobile = isMobileDevice();
    var questions = [intro_question_1, intro_question_2, intro_question_3];
    const icebreakersToShow = isMobile ? [questions[0]] : questions; // Показываем только первый вопрос на мобильных устройствах
    const dialog = document.getElementById('asstgpt-dialog');
    icebreakersToShow.forEach(question => {
      if (question) {
        const messageElement = document.createElement('div');
        messageElement.classList.add('gpt-message', 'intro-question');
        messageElement.innerHTML = `<a href="#" class="icebreaker-link">${question}</a>`;
        dialog.appendChild(messageElement);
      }
    });


    const icebreakerLinks = document.getElementsByClassName('icebreaker-link');
    for (let i = 0; i < icebreakerLinks.length; i++) {
      icebreakerLinks[i].addEventListener('click', function (event) {
        event.preventDefault();
        const input = document.getElementById('asstgpt-input');
        input.value = this.textContent;
        sendChatGPTMessage();
      });
    }
  });
</script>
