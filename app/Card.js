import React, { Fragment, useState } from "react";
import styled from "styled-components";
import { LinkButton } from "./LinkButton";
import { urlTitle } from "./url-title";

const CardDiv = styled.div`
  padding: 20px;
  box-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
  border-radius: 4px;
  max-width: 20em;
  border: 1px solid #eef;
  margin: 2em auto;
  background-color: ${(props) => (props.reverse ? "#ffd9e0" : "white")};
  position: relative;
  min-height: 6em;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  & .corner {
    position: absolute;
    opacity: 0.7;
    right: 10px;
    top: 10px;
    font-size: 90%;
  }
  & ul {
    padding: 0;
  }
  & li {
    font-size: 10pt;
    display: inline-block;
  }
  & li + li {
    margin-left: 0.5em;
  }
  & .h {
    font-weight: bold;
  }
`;

export const Card = ({ card, show, onShow, onChange }) => {
  const [editing, setEditing] = useState(false);
  return (
    <CardDiv reverse={card.reverse} onClick={onShow}>
      <div className="corner" title="score">
        {card.score}
      </div>
      <p className="h">{card.q}</p>
      {show && (
        <>
          {editing ? (
            <Editor
              card={card}
              onChange={(newCard) => {
                onChange(newCard);
                setEditing(false);
              }}
            />
          ) : (
            <>
              {card.a.split("\n").map((line, i) => (
                <Fragment key={i}>
                  {line}
                  <br />
                </Fragment>
              ))}
              <LinkButton
                onClick={() => {
                  setEditing(true);
                }}
              >
                Edit
              </LinkButton>
            </>
          )}
          <ul>
            {card.urls.map((url) => (
              <li key={url}>
                <a target="_blank" rel="noreferrer" href={url}>
                  {urlTitle(url)}
                </a>
              </li>
            ))}
          </ul>
        </>
      )}
    </CardDiv>
  );
};

const Editor = ({ card, onChange }) => {
  const [q, setQ] = useState(card.q);
  const [a, setA] = useState(card.a);
  return (
    <>
      <input
        value={q}
        onChange={(e) => {
          setQ(e.target.value);
        }}
      />
      <br />
      <textarea
        value={a}
        onChange={(e) => {
          setA(e.target.value);
        }}
      />
      <LinkButton
        onClick={() => {
          onChange({ ...card, q, a });
        }}
      >
        Save
      </LinkButton>
    </>
  );
};
