import React, { useRef } from "react";
import { withRouter } from "react-router";
import styled from "styled-components";
import Resource from "./Resource";
import withAPI from "./withAPI";

const Form = styled.form`
  & > div {
    columns: 20em;
    border-bottom: 1px dashed #bebedb;
    padding-bottom: 1em;
    margin-bottom: 2em;
  }
  & > div > section {
    vertical-align: top;
    margin-bottom: 1em;
  }
  & > div > section > div {
    display: flex;
    flex-direction: column;
    margin-bottom: 0.5em;
  }
`;

export default withRouter(
  withAPI(({ api, busy, history, dictID }) => {
    const handleSubmit = async (entries) => {
      const results = await api.submitAnswers(dictID, entries);
      localStorage.setItem(`results-${dictID}`, JSON.stringify(results));
      history.push(`results`);
    };

    const focused = useRef(false);

    return (
      <Resource getPromise={() => api.test(dictID)}>
        {(data) => (
          <Form
            ref={(form) => {
              if (!form) {
                return;
              }
              if (focused.current) {
                return;
              }
              focused.current = true;
              form.querySelector('input[name="a[]"]').focus();
            }}
            method="post"
            className="test-form"
            onFocus={(e) => {
              e.target.scrollIntoView({ behavior: "smooth", block: "center" });
            }}
            onSubmit={(e) => {
              e.preventDefault();
              const r = [...e.target.querySelectorAll("input")].map((input) => [
                input.name,
                input.value,
              ]);
              handleSubmit(r);
            }}
          >
            <div>
              <section>
                {data.tuples1.map((question) => (
                  <div key={question.id}>
                    <input type="hidden" name="q[]" value={question.id} />
                    <label>
                      {question.q} <small>({question.times})</small>
                    </label>
                    <input name="a[]" autoComplete="off" />
                  </div>
                ))}
              </section>
            </div>
            <button type="submit" disabled={busy}>
              Submit
            </button>
          </Form>
        )}
      </Resource>
    );
  })
);
