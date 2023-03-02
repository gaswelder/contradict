import React, { useEffect, useState } from "react";
import styled from "styled-components";
import withAPI from "../components/withAPI";
import { Card } from "./Card";

const ContainerDiv = styled.div`
  text-align: center;
`;

const ClipDiv = styled.div`
  margin-top: 1em;
  & span {
    display: inline-block;
    border: thin solid #cce;
    border-radius: 2px;
    padding: 4px;
    font-size: 8pt;
    line-height: 8px;
    margin: 2px;
  }
`;

export const RepetitionsPage = withAPI(({ api, busy, dictID }) => {
  const [cards, setCards] = useState([]);
  const [show, setShow] = useState(false);
  const [yes, setYes] = useState(false);

  const nextBatch = async () => {
    const r = await api.test(dictID);
    setCards(r.tuples1);
  };

  const next = async () => {
    setShow(false);
    setYes(false);
    if (cards.length == 1) {
      await nextBatch();
    } else {
      setCards(cards.slice(1));
    }
  };

  useEffect(() => {
    nextBatch();
  }, []);

  const card = cards[0];
  if (!card) {
    return "loading";
  }
  return (
    <ContainerDiv>
      <ClipDiv>
        {cards.map((card) => (
          <span key={card.id}> {card.score} </span>
        ))}
      </ClipDiv>
      <Card
        card={card}
        show={show}
        onShow={() => {
          if (show) {
            return;
          }
          setShow(true);
          api.touchCard(dictID, card.id, card.reverse, false);
        }}
        onChange={(newCard) => {
          api.updateEntry(dictID, card.id, { q: newCard.q, a: newCard.a });
          setCards([newCard, ...cards.slice(1)]);
        }}
      />
      {show && (
        <>
          {yes && (
            <>
              <button
                onClick={() => {
                  api.touchCard(dictID, card.id, card.reverse, false);
                  api.touchCard(dictID, card.id, card.reverse, false);
                  next();
                }}
              >
                Oops, wrong guess
              </button>{" "}
            </>
          )}
          <button onClick={next}>Next</button>
        </>
      )}
      {!show && (
        <>
          <button
            onClick={async () => {
              api.touchCard(dictID, card.id, card.reverse, false);
              setShow(true);
            }}
          >
            No, forgot it
          </button>{" "}
          <button
            disabled={busy}
            onClick={() => {
              api.touchCard(dictID, card.id, card.reverse, true);
              setShow(true);
              setYes(true);
            }}
          >
            Yes, know it
          </button>
        </>
      )}
    </ContainerDiv>
  );
});
