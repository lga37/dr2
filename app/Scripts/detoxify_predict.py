#!/usr/bin/env python3
import sys
import json
from detoxify import Detoxify

def main():
    raw = sys.stdin.read()

    try:
        payload = json.loads(raw)
    except Exception:
        print(json.dumps({"error": "invalid_json"}))
        return

    text = payload.get("text", "")
    model_name = payload.get("model", "multilingual")

    if not text.strip():
        print(json.dumps({"error": "empty_text"}))
        return

    try:
        model = Detoxify(model_name)
        result = model.predict(text)

        clean = {}
        for k, v in result.items():
            try:
                clean[k] = float(v)
            except Exception:
                clean[k] = v

        print(json.dumps({
            "model": model_name,
            "result": clean
        }, ensure_ascii=False))

    except Exception as e:
        print(json.dumps({
            "error": str(e)
        }, ensure_ascii=False))

if __name__ == "__main__":
    main()
