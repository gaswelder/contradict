import React, { useEffect, useState } from "react";
import styled from "styled-components";
import { Card } from "./Card";
import { useAPI } from "./withAPI";

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

const store = new Map();

const useGlobalState = (initial, key) => {
  const initialized = store.has(key);
  const [val, set] = useState(initialized ? store.get(key) : initial);
  useEffect(() => {
    store.set(key, val);
  }, [val]);
  return [val, set, initialized];
};

export const RepetitionsPage = ({ dictID }) => {
  const { api, busy } = useAPI();
  const [cards, setCards, initialized] = useGlobalState(
    [],
    "repetitions/" + dictID
  );
  const [show, setShow] = useState(false);
  const [yes, setYes] = useState(false);
  const [count, setCount] = useState(0);

  const nextBatch = async () => {
    const r = await api.test(dictID);
    setCards(r.tuples1.map((c) => ({ ...c, inverse: Math.random() < 0.2 })));
  };

  const next = async () => {
    setCount((x) => x + 1);
    setShow(false);
    setYes(false);
    setCards(cards.slice(1));
  };

  useEffect(() => {
    !initialized && nextBatch();
  }, []);

  const card = cards[0];
  return (
    <ContainerDiv>
      {count}
      <ClipDiv>
        {cards.map((card) => (
          <span key={card.id}>{card.score}</span>
        ))}
      </ClipDiv>
      {!cards.length && (
        <button disabled={busy} onClick={nextBatch}>
          Load Next Batch
        </button>
      )}
      {card && (
        <>
          <Card
            card={card}
            show={show}
            inverse={card.inverse}
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
        </>
      )}
    </ContainerDiv>
  );
};
