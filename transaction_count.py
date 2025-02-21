import random

def get_top3_most_purchased(transactions):
    """
    Given a list of transactions (each representing a purchased item),
    return the top 3 most-purchased items along with their counts.

    :param transactions: List of items (could be strings, numbers, etc.)
    :return: A list of tuples: [(item, count), ...] for the top 3 items.
    """
    # Manually count frequencies using a dictionary.
    freq = {}
    for item in transactions:
        if item in freq:
            freq[item] += 1
        else:
            freq[item] = 1

    # Sort items by count in descending order and pick the top 3.
    top3 = sorted(freq.items(), key=lambda x: x[1], reverse=True)[:3]
    return top3

# Example usage:
if __name__ == '__main__':
    # Simulate 1 million transactions from a small set of sample items.
    sample_items = ["itemA", "itemB", "itemC", "itemD", "itemE"]
    transactions = [random.choice(sample_items) for _ in range(1_000_000)]

    top3 = get_top3_most_purchased(transactions)
    print("Top 3 most-purchased items:")
    for item, count in top3:
        print(f"{item}: {count}")
